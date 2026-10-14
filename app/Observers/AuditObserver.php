<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Generic create/update/delete audit trail for any model it's attached
 * to. Registered in AppServiceProvider for the business-critical models
 * (Medicine, Purchase, MedicineIssueVoucher, StockAdjustment, Employee,
 * Supplier, User) so every change to financial or inventory data is
 * traceable to a user, per the "never silently edit posted transactions"
 * requirement.
 */
class AuditObserver
{
    public function created(Model $model): void
    {
        $this->log('created', $model, null, $model->getAttributes());
    }

    public function updated(Model $model): void
    {
        $this->log('updated', $model, $model->getOriginal(), $model->getChanges());
    }

    public function deleted(Model $model): void
    {
        $this->log('deleted', $model, $model->getAttributes(), null);
    }

    protected function log(string $action, Model $model, ?array $old, ?array $new): void
    {
        if (! Auth::check()) {
            return;
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'auditable_type' => get_class($model),
            'auditable_id' => $model->getKey(),
            'old_values' => $old,
            'new_values' => $new,
            'ip_address' => Request::ip(),
            'url' => Request::fullUrl(),
        ]);
    }
}
