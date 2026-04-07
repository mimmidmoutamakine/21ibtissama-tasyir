<?php

namespace App\Providers;

use App\Models\Beneficiary;
use App\Models\AnnualProject;
use App\Models\MonthlyPlan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        Carbon::setLocale('ar');

        Gate::define('access-beneficiary', function ($user, Beneficiary $beneficiary) {
            if ($user->hasRole('admin')) {
                return true;
            }

            if ($user->hasRole('educator')) {
                return $beneficiary->primary_educator_id === $user->id;
            }

            if ($user->hasRole('specialist')) {
                return $beneficiary->serviceAssignments()->where('specialist_id', $user->id)->exists();
            }

            return false;
        });

        Gate::define('manage-monthly-plan', function ($user, MonthlyPlan $monthlyPlan) {
            if ($user->hasRole('admin')) {
                return true;
            }

            if ($user->hasRole('educator')) {
                return $monthlyPlan->educator_id === $user->id;
            }

            if ($user->hasRole('specialist')) {
                return $monthlyPlan->beneficiary->serviceAssignments()->where('specialist_id', $user->id)->exists();
            }

            return false;
        });

        Gate::define('access-annual-project', function ($user, AnnualProject $annualProject) {
            $beneficiary = $annualProject->beneficiary;

            if (! $beneficiary) {
                return false;
            }

            if ($user->hasRole('admin')) {
                return true;
            }

            if ($user->hasRole('educator')) {
                return $beneficiary->primary_educator_id === $user->id;
            }

            if ($user->hasRole('specialist')) {
                return $beneficiary->serviceAssignments()->where('specialist_id', $user->id)->exists();
            }

            return false;
        });
    }
}
