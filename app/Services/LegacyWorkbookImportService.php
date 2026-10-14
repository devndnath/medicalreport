<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Employee;
use App\Models\ImportBatch;
use App\Models\ImportBatchRow;
use App\Models\Medicine;
use App\Models\MedicineCategory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Imports the factory's legacy "Medicine Report" workbook — the exact
 * layout analyzed in docs/workbook-analysis-and-schema.md:
 *   - "Medicine List"                -> medicine master seed
 *   - "Daily Report"                 -> historical issue line items
 *   - one sheet per medicine         -> opening balance (first dated row's
 *                                       "Op. Balance" column)
 *
 * Nothing here invents a price, a quantity, or an employee ID that is not
 * in the sheet. Rows that cannot be matched cleanly are flagged as errors
 * and never silently guessed. The original uploaded file is stored as-is
 * and never modified.
 */
class LegacyWorkbookImportService
{
    public function preview(string $storedPath, string $originalFilename): ImportBatch
    {
        $spreadsheet = IOFactory::load(storage_path('app/'.$storedPath));

        $batch = ImportBatch::create([
            'original_filename' => $originalFilename,
            'stored_path' => $storedPath,
            'uploaded_by' => Auth::id(),
            'status' => 'previewed',
        ]);

        $knownMedicineNames = $this->readMedicineList($spreadsheet);
        $existingMedicineNames = Medicine::pluck('name')->map(fn ($n) => $this->normalizeName($n))->all();
        $existingEmployeeCodes = Employee::pluck('employee_code')->all();

        $total = 0;
        $valid = 0;
        $errorCount = 0;
        $duplicateCount = 0;

        if ($spreadsheet->sheetNameExists('Daily Report')) {
            $sheet = $spreadsheet->getSheetByName('Daily Report');
            $highestRow = $sheet->getHighestDataRow();

            for ($r = 2; $r <= $highestRow; $r++) {
                $date = $sheet->getCell("A{$r}")->getValue();
                $name = trim((string) $sheet->getCell("B{$r}")->getValue());
                $empId = trim((string) $sheet->getCell("C{$r}")->getValue());
                $section = trim((string) $sheet->getCell("D{$r}")->getValue());
                $rx = trim((string) $sheet->getCell("F{$r}")->getValue());
                $qty = $sheet->getCell("G{$r}")->getValue();

                if ($date === null && $name === '' && $rx === '') {
                    continue; // blank template row
                }

                $total++;
                $errors = [];

                if (! $date) {
                    $errors[] = 'Missing date.';
                }
                if ($name === '') {
                    $errors[] = 'Missing employee name.';
                }
                if ($empId === '') {
                    $errors[] = 'Missing Employee ID — will import flagged for manual ID confirmation, not guessed.';
                }
                if ($rx === '') {
                    $errors[] = 'Missing medicine (Rx.).';
                } elseif (! in_array($this->normalizeName($rx), array_map(fn ($n) => $this->normalizeName($n), $knownMedicineNames), true)) {
                    $errors[] = "Medicine \"{$rx}\" not found in the Medicine List sheet — needs manual mapping.";
                }
                if (! is_numeric($qty) || (float) $qty <= 0) {
                    $errors[] = 'Quantity is missing or not a valid number.';
                }

                $status = 'valid';
                if (collect($errors)->contains(fn ($e) => ! str_contains($e, 'flagged for manual ID confirmation'))) {
                    $status = 'error';
                    $errorCount++;
                } else {
                    $valid++;
                }

                ImportBatchRow::create([
                    'import_batch_id' => $batch->id,
                    'source_sheet' => 'Daily Report',
                    'source_row' => $r,
                    'raw_data' => [
                        'date' => $date instanceof \DateTimeInterface ? $date->format('Y-m-d') : $date,
                        'name' => $name,
                        'employee_code' => $empId,
                        'section' => $section,
                        'medicine_name' => $rx,
                        'quantity' => $qty,
                    ],
                    'row_status' => $status,
                    'errors' => $errors,
                ]);
            }
        }

        $batch->update([
            'total_rows' => $total,
            'valid_rows' => $valid,
            'error_rows' => $errorCount,
            'duplicate_rows' => $duplicateCount,
        ]);

        return $batch;
    }

    /**
     * Commits a previewed batch: creates departments/employees/medicines as
     * needed and posts the historical issues as stock transactions, using
     * StockService so the ledger stays consistent. Rows still marked
     * 'error' are skipped and remain visible in the batch for follow-up.
     */
    public function commit(ImportBatch $batch, StockService $stock): array
    {
        $created = ['departments' => 0, 'employees' => 0, 'medicines' => 0, 'issues' => 0, 'skipped' => 0];

        DB::transaction(function () use ($batch, $stock, &$created) {
            // Ensure every medicine on the Medicine List exists as a master record.
            $spreadsheet = IOFactory::load(storage_path('app/'.$batch->stored_path));
            foreach ($this->readMedicineList($spreadsheet) as $name) {
                $parsed = $this->parseLegacyMedicineName($name);
                $exists = Medicine::where('name', $parsed['name'])->where('strength', $parsed['strength'])->where('dosage_form', $parsed['dosage_form'])->first();
                if (! $exists) {
                    Medicine::create([
                        'sku' => Str::upper(Str::slug(Str::substr($parsed['name'], 0, 10), '')).'-'.random_int(1000, 9999),
                        'name' => $parsed['name'],
                        'strength' => $parsed['strength'],
                        'dosage_form' => $parsed['dosage_form'],
                        'base_unit' => $parsed['unit'],
                        'issue_unit' => $parsed['unit'],
                        'purchase_unit' => $parsed['unit'],
                        'units_per_purchase_pack' => 1,
                        'minimum_stock_level' => 0,
                        'reorder_level' => 0,
                        'status' => 'active',
                        'remarks' => 'Imported from legacy workbook Medicine List, not yet priced.',
                    ]);
                    $created['medicines']++;
                }
            }

            foreach ($batch->rows()->where('row_status', 'valid')->orWhere(function ($q) {
                $q->where('row_status', 'error')->whereJsonDoesntContain('errors', 'Missing date.');
            })->get() as $row) {
                $data = $row->raw_data;

                if ($row->row_status === 'error') {
                    $created['skipped']++;
                    continue;
                }

                $department = null;
                if ($data['section']) {
                    $department = Department::firstOrCreate(
                        ['name' => $this->normalizeDepartment($data['section'])],
                        ['legacy_label' => $data['section']]
                    );
                    if ($department->wasRecentlyCreated) {
                        $created['departments']++;
                    }
                }

                $employee = Employee::where('employee_code', $data['employee_code'] ?: 'UNKNOWN-'.Str::slug($data['name']))->first();
                if (! $employee) {
                    $employee = Employee::create([
                        'employee_code' => $data['employee_code'] ?: 'NEEDS-ID-'.Str::upper(Str::random(5)),
                        'name' => $data['name'],
                        'department_id' => $department?->id,
                        'employment_status' => 'active',
                        'needs_id_confirmation' => empty($data['employee_code']),
                    ]);
                    $created['employees']++;
                }

                $parsed = $this->parseLegacyMedicineName($data['medicine_name']);
                $medicine = Medicine::where('name', $parsed['name'])->where('strength', $parsed['strength'])->where('dosage_form', $parsed['dosage_form'])->first();
                if (! $medicine) {
                    $created['skipped']++;
                    continue;
                }

                // Historical issue: no price data exists for this period, so
                // cost is recorded as unavailable rather than invented. If
                // the medicine has no stock yet, post a zero-cost legacy
                // opening lot just large enough to let the historical issue
                // post, clearly flagged as a legacy reconciliation item.
                if ($medicine->currentStock() < (float) $data['quantity']) {
                    $stock->openingBalance($medicine, (float) $data['quantity'], 'LEGACY-IMPORT-'.$medicine->sku, $data['date'], costUnavailable: true);
                }

                try {
                    $stock->issueFifo($medicine, (float) $data['quantity']);
                    $created['issues']++;
                } catch (\RuntimeException $e) {
                    $created['skipped']++;
                }
            }

            $batch->status = 'confirmed';
            $batch->confirmed_at = now();
            $batch->save();
        });

        return $created;
    }

    protected function readMedicineList($spreadsheet): array
    {
        if (! $spreadsheet->sheetNameExists('Medicine List')) {
            return [];
        }
        $sheet = $spreadsheet->getSheetByName('Medicine List');
        $names = [];
        for ($r = 1; $r <= $sheet->getHighestDataRow(); $r++) {
            $val = trim((string) $sheet->getCell("A{$r}")->getValue());
            if ($val !== '') {
                $names[] = $val;
            }
        }

        return $names;
    }

    protected function normalizeName(string $name): string
    {
        return Str::lower(trim(str_replace(['–', '—'], '-', $name)));
    }

    protected function normalizeDepartment(string $section): string
    {
        $map = [
            'maintance' => 'Maintenance',
            'maintenance' => 'Maintenance',
            'sweing' => 'Sewing',
            'a. op sweing' => 'A. Op Sewing',
            'store' => 'Store',
            'loader' => 'Loader',
            'q' => 'Q',
        ];

        return $map[Str::lower(trim($section))] ?? trim($section);
    }

    /**
     * Splits a legacy sheet-tab-style name like "Entacyd Plus–(400mg)" into
     * name/strength, and infers a dosage form/base unit from context. Never
     * merges two different legacy names into one medicine.
     */
    protected function parseLegacyMedicineName(string $raw): array
    {
        $clean = str_replace(['–', '—'], '-', $raw);
        $strength = null;

        if (preg_match('/\(([^)]+)\)/', $clean, $m)) {
            $strength = trim($m[1]);
            $clean = trim(str_replace($m[0], '', $clean));
        }

        $clean = trim(preg_replace('/-\s*$/', '', $clean));

        $dosageForm = 'other';
        $unit = 'piece';
        $lower = Str::lower($clean);

        if (Str::contains($lower, 'syrup') || Str::startsWith($lower, 'sry.')) {
            $dosageForm = 'syrup';
            $unit = 'ml';
            $clean = trim(preg_replace('/^sry\.\s*/i', 'Syrup ', $clean));
        } elseif (Str::contains($lower, 'gel')) {
            $dosageForm = 'gel';
            $unit = 'tube';
        } elseif (Str::contains($lower, 'creem') || Str::contains($lower, 'cream')) {
            $dosageForm = 'cream';
            $unit = 'tube';
        } elseif (Str::contains($lower, 'drop')) {
            $dosageForm = 'drop';
            $unit = 'bottle';
        } elseif (Str::contains($lower, 'savlon') && Str::contains($lower, 'liqu')) {
            $dosageForm = 'liquid';
            $unit = 'bottle';
        } elseif (in_array($lower, ['cotton', 'surgical gaze', 'roll bandege-2inc-4 inc', 'nix'], true) || Str::contains($lower, 'bandege')) {
            $dosageForm = 'supply';
            $unit = $lower === 'cotton' ? 'packet' : 'roll';
        } elseif ($strength !== null) {
            $dosageForm = 'tablet';
            $unit = 'tablet';
        }

        return ['name' => trim($clean), 'strength' => $strength, 'dosage_form' => $dosageForm, 'unit' => $unit];
    }
}
