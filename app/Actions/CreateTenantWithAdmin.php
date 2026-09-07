<?php

namespace App\Actions;

use App\Enums\TenantRole;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateTenantWithAdmin
{
    public function handle(User $user, string $name): Tenant
    {
        return DB::transaction(function () use ($user, $name): Tenant {
            $tenant = Tenant::query()->create([
                'name' => $name,
                'slug' => $this->uniqueSlug($name),
            ]);

            $tenant->users()->attach($user->id, [
                'role' => TenantRole::Admin->value,
            ]);

            return $tenant;
        });
    }

    protected function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $base = $base !== '' ? $base : 'tenant';
        $slug = $base;
        $counter = 1;

        while (Tenant::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
