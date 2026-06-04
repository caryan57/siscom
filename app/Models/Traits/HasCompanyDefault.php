<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Model;

/**
 * Garantiza un único registro is_default por company_id.
 *
 * @mixin Model
 */
trait HasCompanyDefault
{
    public static function bootHasCompanyDefault(): void
    {
        static::creating(function (Model $model) {
            $companyId = $model->company_id ?? current_company_id();

            $isFirst = static::where('company_id', $companyId)->count() === 0;

            if ($isFirst) {
                $model->is_default = true;

                return;
            }

            if (static::defaultExistsForCompany($companyId)) {
                $model->is_default = false;
            }
        });

        static::saving(function (Model $model) {
            if ($model->is_default) {
                static::query()
                    ->where('company_id', $model->company_id)
                    ->where('is_default', true)
                    ->when($model->exists, fn ($query) => $query->where('id', '!=', $model->id))
                    ->update(['is_default' => false]);
            } else {
                $existsOtherDefault = static::query()
                    ->where('company_id', $model->company_id)
                    ->where('is_default', true)
                    ->when($model->exists, fn ($query) => $query->where('id', '!=', $model->id))
                    ->exists();

                if (! $existsOtherDefault) {
                    $model->is_default = true;
                }
            }
        });

        static::deleting(function (Model $model) {
            if ($model->is_default) {
                throw new \Exception($model->companyDefaultDeletionMessage());
            }

            if ($model->isUsedForCompanyDefaultDeletion()) {
                throw new \Exception($model->companyDefaultInUseDeletionMessage());
            }
        });
    }

    protected static function defaultExistsForCompany(?int $companyId): bool
    {
        return static::where('company_id', $companyId)
            ->where('is_default', true)
            ->exists();
    }

    public function getCompanyDefaultDeletionMessage(): string
    {
        return $this->companyDefaultDeletionMessage();
    }

    public function cannotDeleteBecauseInUse(): bool
    {
        return $this->isUsedForCompanyDefaultDeletion();
    }

    public function getCompanyDefaultInUseDeletionMessage(): string
    {
        return $this->companyDefaultInUseDeletionMessage();
    }

    protected function companyDefaultDeletionMessage(): string
    {
        return 'No puedes eliminar el valor predeterminado.';
    }

    protected function isUsedForCompanyDefaultDeletion(): bool
    {
        return false;
    }

    protected function companyDefaultInUseDeletionMessage(): string
    {
        return 'No puedes eliminar este registro porque está siendo usado.';
    }
}
