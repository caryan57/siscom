<?php

namespace App\Services;

use App\Enums\PriceListType;
use App\Enums\ProductAttributeType;
use App\Enums\RoleType;
use App\Enums\TaxType;
use App\Enums\UnitType;
use App\Models\Attribute;
use App\Models\Permission;
use App\Models\PriceList;
use App\Models\Role;
use App\Models\Tax;
use App\Models\Unit;
use Spatie\Permission\PermissionRegistrar;

class CompanySetupService
{
    public function setupDefaults(int $companyId): void
    {
        $this->createRoles($companyId);
        $this->createUnits($companyId);
        $this->createTaxes($companyId);
        $this->createPriceLists($companyId);
        $this->createAttributes($companyId);
    }

    private function createUnits(int $companyId): void
    {
        foreach (UnitType::cases() as $unit) {
            Unit::withoutEvents(function () use ($unit, $companyId) {
                Unit::create([...$unit->toArray(), 'company_id' => $companyId]);
            });
        }
    }

    private function createTaxes(int $companyId): void
    {
        foreach (TaxType::cases() as $tax) {
            Tax::withoutEvents(function () use ($tax, $companyId) {
                Tax::create([...$tax->toArray(), 'company_id' => $companyId]);
            });
        }
    }

    private function createPriceLists(int $companyId): void
    {
        foreach (PriceListType::cases() as $priceList) {
            PriceList::withoutEvents(function () use ($priceList, $companyId) {
                PriceList::create([...$priceList->toArray(), 'company_id' => $companyId]);
            });
        }
    }

    private function createAttributes(int $companyId): void
    {
        foreach (ProductAttributeType::cases() as $attribute) {
            Attribute::withoutEvents(function () use ($attribute, $companyId) {
                Attribute::create([...$attribute->toArray(), 'company_id' => $companyId]);
            });
        }
    }

    private function createRoles(int $companyId): void
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId($companyId);

        foreach (RoleType::cases() as $role) {
            Role::firstOrCreate([
                'name' => $role->value,
                'guard_name' => 'web',
                'company_id' => $companyId,
            ]);
        }

        // Asignar permisos al rol de owner
        $ownerRole = Role::where('name', 'owner')
            ->where('company_id', $companyId)
            ->first();
        $ownerRole->syncPermissions(Permission::all());

        // Asignar los permisos al admin de la empresa, no puede modificar roles, solo si es owner.
        $adminRole = Role::where('name', 'admin')
            ->where('company_id', $companyId)
            ->first();

        $excludedPermissions = [
            'ViewAny:Role',
            'View:Role',
            'Create:Role',
            'Update:Role',
            'Delete:Role',
            'DeleteAny:Role',
            'Restore:Role',
            'ForceDelete:Role',
            'ForceDeleteAny:Role',
            'RestoreAny:Role',
            'Replicate:Role',
            'Reorder:Role',
            'View:Onboarding',
        ];

        $adminPermissions = Permission::whereNotIn('name', $excludedPermissions)->get();
        $adminRole->syncPermissions($adminPermissions);
    }
}
