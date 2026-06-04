<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\Branch;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterSave(): void
    {
        $companyId = current_company_id();

        $this->record->unsetRelation('roles');

        // Owner does not require any relationship with branches and detach all previous relations.
        if ($this->record->isOwner()) {
            $this->record->branches()->detach();
            return;
        };

        
        $branches = Branch::where('company_id', $companyId)->get();

        // If there is only one branch, assign only to that one.
        if ($branches->count() === 1) {
            $this->record->branches()->sync([$branches->first()->id]);
            return;
        };

        $selectedBranches = $this->data['branches'] ?? [];
        // Update new branch changes
        $this->record->branches()->sync($selectedBranches);        
    }
}
