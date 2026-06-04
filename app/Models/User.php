<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\RoleType;
use App\Enums\Status;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['uuid', 'name', 'surname', 'email', 'password', 'status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasUuid;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => Status::class
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class, 'company_user')
            ->withTimestamps();;
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'branch_users')
            ->using(BranchUser::class)
            ->withPivot(['company_id'])
            ->withTimestamps();
    }

    public function companyMemberships()
    {
        return $this->hasMany(CompanyUser::class);
    }

    public function branchAssignments()
    {
        return $this->hasMany(BranchUser::class);
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function isOwner(): bool
    {
        return $this->hasRole(RoleType::OWNER->value);
    }

    public function getRoleLabelAttribute(): ?string
    {
        $role = $this->roles()->first()?->name;
        if (!$role) return null;

        return RoleType::tryFrom($role)?->getLabel() ?? ucfirst($role);
    }
}
