<?php

declare(strict_types=1);

namespace App\Filament\Resources\Roles\Pages;

use App\Enums\RoleType;
use App\Filament\Resources\Roles\RoleResource;
use App\Models\Role;
use BezhanSalleh\FilamentShield\Support\Utils;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Override;

class CreateRole extends CreateRecord
{
    public Collection $permissions;

    protected static string $resource = RoleResource::class;

    #[Override]
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['guard_name'] = $data['guard_name'] ?? Utils::getFilamentAuthGuard();
        $data[Utils::getTenantModelForeignKey()] = current_company_id();
        $data['name'] = trim((string) $data['name']);

        $this->validateRoleName($data['name'], $data['guard_name'], $data[Utils::getTenantModelForeignKey()]);

        $this->permissions = collect($data)
            ->filter(fn (mixed $permission, string $key): bool => ! in_array($key, ['name', 'guard_name', 'select_all', Utils::getTenantModelForeignKey()], true))
            ->values()
            ->flatten()
            ->unique();

        return Arr::only($data, ['name', 'guard_name', Utils::getTenantModelForeignKey()]);
    }

    private function validateRoleName(string $name, string $guardName, int $companyId): void
    {
        $normalizedName = Str::lower($name);

        $reservedNames = collect(RoleType::cases())
            ->flatMap(fn (RoleType $role): array => [$role->value, $role->getLabel()])
            ->map(fn (string $role): string => Str::lower($role));

        if ($reservedNames->contains($normalizedName)) {
            throw ValidationException::withMessages([
                'data.name' => 'Este nombre pertenece a un rol del sistema. Usa otro nombre para continuar.',
            ]);
        }

        $exists = Role::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('guard_name', $guardName)
            ->whereRaw('LOWER(TRIM(name)) = ?', [$normalizedName])
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'data.name' => 'Ya existe un rol con este nombre.',
            ]);
        }
    }

    protected function afterCreate(): void
    {
        $permissionModels = collect();
        $this->permissions->each(function (string $permission) use ($permissionModels): void {
            $permissionModels->push(Utils::getPermissionModel()::firstOrCreate([
                'name' => $permission,
                'guard_name' => $this->data['guard_name'],
            ]));
        });

        $this->record->syncPermissions($permissionModels);
    }
}
