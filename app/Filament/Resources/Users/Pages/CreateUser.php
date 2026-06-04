<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\Branch;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
    protected static bool $canCreateAnother = false;

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $companyId = current_company_id();

            $selectedBranches = $data['branches'] ?? [];
            unset($data['branches']);

            /** @var User $user */
            $user = static::getModel()::create($data);

            // Relation of user with company
            $user->companies()->attach($companyId);

            // Relation of user with branches
            $this->branchesRelationship($user, $companyId, $selectedBranches);

            return $user;
        });
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    private function branchesRelationship(User $user, ?int $companyId, array $selectedBranches): void
    {
        // Owner does not require any relationship with branches
        if ($user->isOwner()) return;

        $branches = Branch::where('company_id', $companyId)->get();

        if ($branches->count() === 1) {
            // Auto-assign to the only branch existing
            $branches->first()->users()->attach($user->id);
        } else {
            foreach ($branches->whereIn('id', $selectedBranches) as $branch) {
                $branch->users()->attach($user->id);
            }
        }
    }
}
