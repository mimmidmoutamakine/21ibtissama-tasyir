<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Beneficiary;
use App\Models\MonthlyPlan;

trait AuthorizesAssociationAccess
{
    protected function ensureBeneficiaryAccess(Beneficiary $beneficiary): void
    {
        abort_unless(auth()->user()->can('access-beneficiary', $beneficiary), 403);
    }

    protected function ensureMonthlyPlanAccess(MonthlyPlan $monthlyPlan): void
    {
        abort_unless(auth()->user()->can('manage-monthly-plan', $monthlyPlan), 403);
    }
}
