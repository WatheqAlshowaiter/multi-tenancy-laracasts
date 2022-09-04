<?php

use App\Models\Tenant;
use App\Scopes\TenantScope;

trait BelongsToTenant
{
    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function bootBelongsToTenant()
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($user) {
            if (session()->has('tenant_id')) {
                $user->tenant_id = session()->get('tenant_id');
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
