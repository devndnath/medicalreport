<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Employee Expense Report</title>
<style>body{font-family: DejaVu Sans, Arial, sans-serif; font-size:12px;} table{width:100%;border-collapse:collapse;} th,td{border-bottom:1px solid #e2e8f0;text-align:left;padding:6px;}</style>
</head><body>
<h2>Employee-wise Medicine Expense Report</h2>
<p>{{ $from->format('d-m-Y') }} to {{ $to->format('d-m-Y') }}</p>
<table><thead><tr><th>Employee</th><th>Department</th><th>Qty</th><th>Cost</th></tr></thead>
<tbody>
@foreach($rows as $row)
<tr><td>{{ $row['employee']->name }}</td><td>{{ $row['employee']->department->name ?? '—' }}</td><td>{{ number_format($row['quantity'],2) }}</td><td>{{ number_format($row['cost'],2) }}</td></tr>
@endforeach
</tbody></table>
</body></html>
