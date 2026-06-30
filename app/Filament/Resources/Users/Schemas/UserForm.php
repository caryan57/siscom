<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\RoleType;
use App\Enums\Status;
use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información General')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required(),
                        TextInput::make('surname')
                            ->label('Apellidos')
                            ->required(),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->unique(
                                table: 'users',
                                column: 'email',
                                ignoreRecord: true,
                            )
                            ->validationMessages([
                                'unique' => 'Ya existe un usuario con este email.',
                            ])
                            ->required(),
                        Select::make('status')
                            ->options(Status::class)
                            ->default(Status::Active->value)
                            ->required()
                            ->disabled(fn ($record) => Auth::id() === $record?->id)
                            ->dehydrated(fn ($record) => Auth::id() !== $record?->id),
                        Select::make('roles')
                            ->label('Rol')
                            ->required()
                            ->live()
                            ->relationship(
                                name: 'roles',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query): Builder =>
                                $query->where('company_id', current_company_id()),
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn ($record) => RoleType::tryFrom($record->name)?->getLabel() ?? ucfirst($record->name)
                            )
                            ->afterStateUpdated(function (callable $set) {
                                $branches = Branch::where('company_id', current_company_id())->get();
                                if ($branches->count() === 1) {
                                    // Select the only option available
                                    $set('branches', [$branches->first()->id]);
                                } else {
                                    // Else clean options
                                    $set('branches', []);
                                }
                            })
                            ->saveRelationshipsUsing(function (User $record, $state) {
                                $record->roles()->syncWithPivotValues(
                                    [$state],
                                    [config('permission.column_names.team_foreign_key') => current_company_id()]
                                );
                            })
                            ->disabled(fn (?User $record) => Auth::id() === $record?->id),
                    ]),
                Section::make('Seguridad')
                    ->schema([
                        TextInput::make('current_password')
                            ->label('Contraseña actual')
                            ->password()
                            ->revealable()
                            ->visible(
                                fn (string $operation, ?User $record): bool => $operation === 'edit' && $record && Auth::id() === $record->id
                            )
                            ->required(
                                fn (callable $get): bool => filled($get('password'))
                            )
                            ->dehydrated(false)
                            ->rule(function (callable $get) {
                                return function (string $attribute, $value, \Closure $fail) use ($get) {
                                    if (! filled($get('password'))) {
                                        return;
                                    }

                                    if (! Hash::check($value, Auth::user()->password)) {
                                        $fail('La contraseña actual es incorrecta.');
                                    }
                                };
                            }),
                        TextInput::make('password')
                            ->label('Nueva contraseña')
                            ->password()
                            ->revealable()
                            ->visible(function (string $operation, ?User $record): bool {
                                if ($operation === 'create') {
                                    return true;
                                }

                                return $record && Auth::id() === $record->id;
                            })
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->confirmed()
                            ->dehydrated(fn (?string $state): bool => filled($state)),
                        TextInput::make('password_confirmation')
                            ->label('Confirmar contraseña')
                            ->password()
                            ->revealable()
                            ->visible(function (string $operation, ?User $record): bool {
                                if ($operation === 'create') {
                                    return true;
                                }

                                return $record && Auth::id() === $record->id;
                            })
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(false),
                    ])
                    ->visible(
                        fn (string $operation, ?User $record): bool => $operation === 'create' ||
                            ($operation === 'edit' && $record && Auth::id() === $record->id)
                    ),
                Section::make('Sucursales')
                    ->schema([
                        CheckboxList::make('branches')
                            ->label('Selecciona las sucursales que tiene acceso')
                            ->bulkToggleable()
                            ->columns(2)
                            ->options(Branch::where('company_id', current_company_id())->pluck('name', 'id'))
                            ->required(
                                fn (callable $get) => Role::find($get('roles'))?->name !== RoleType::OWNER->value
                            )
                            ->minItems(1)
                            ->afterStateHydrated(function ($component, ?User $record, $state) {
                                if (filled($state)) {
                                    return;
                                }

                                if ($record) {
                                    $component->state(
                                        $record->branches()->pluck('branches.id')->toArray()
                                    );
                                } else {
                                    $branches = Branch::where('company_id', current_company_id())->get();
                                    if ($branches->count() === 1) {
                                        $component->state([$branches->first()->id]);
                                    }
                                }
                            })
                            ->disabled(
                                fn (callable $get) => Branch::where('company_id', current_company_id())->count() === 1
                                    || Role::find($get('roles'))?->name === RoleType::OWNER->value
                            )
                            ->dehydrated(true)
                            ->visible(
                                fn (callable $get, ?User $record) => Role::find($get('roles'))?->name !== RoleType::OWNER->value
                                    && ! (! $get('roles') && $record?->isOwner())
                            ),

                        Text::make('El propietario tiene acceso a todas las sucursales automáticamente.')
                            ->visible(function (callable $get, ?User $record) {
                                $selectedRoleIsOwner = Role::find($get('roles'))?->name === RoleType::OWNER->value;
                                $recordIsOwner = ! $get('roles') && $record?->isOwner();

                                return $selectedRoleIsOwner || $recordIsOwner;
                            }),
                    ])
                    ->visible(true),
            ]);
    }
}
