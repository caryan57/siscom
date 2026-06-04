<?php

namespace App\Filament\Pages;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Role;
use App\Services\CompanySetupService;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;

class Onboarding extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'onboarding';

    public array $data = [];

    public function getView(): string
    {
        return 'filament.pages.onboarding';
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make('Tu empresa')
                        ->schema([
                            Section::make('Logotipo')
                                ->schema([
                                    FileUpload::make('logo')
                                        ->hiddenLabel()
                                        ->image()
                                        ->directory('logos/' . date('Y/m'))
                                        ->visibility('public')
                                        ->getUploadedFileNameForStorageUsing(
                                            fn($file): string => (string) str(Str::uuid() . '.' . $file->getClientOriginalExtension())
                                        )
                                        ->maxSize(1024)
                                        ->imageEditor(),
                                ]),
                            Section::make('Datos de contacto')
                                ->schema([
                                    TextInput::make('company_name')
                                        ->label('Nombre de Empresa')
                                        ->placeholder('Ingresar nombre')
                                        ->required(),
                                    Grid::make(2)
                                        ->schema([
                                            TextInput::make('email')
                                                ->label('Email de Empresa')
                                                ->placeholder('Ingresar email')
                                                ->email(true)
                                                ->nullable(),
                                            TextInput::make('phone')
                                                ->label('Teléfono de Empresa')
                                                ->placeholder('Ingresar teléfono')
                                                ->tel()
                                                ->rule('phone:MX')
                                                ->validationMessages([
                                                    'phone' => 'El teléfono no es válido para México.',
                                                ])
                                                ->nullable(),
                                        ])
                                ])
                        ]),
                    Step::make('Primera sucursal')
                        ->schema([
                            TextInput::make('branch_name')
                                ->label('Nombre de Sucursal')
                                ->placeholder('Ingresar nombre')
                                ->required(),
                            TextInput::make('branch_address')
                                ->label('Dirección')
                                ->placeholder('Ingresar dirección completa')
                                ->nullable(),
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('branch_email')
                                        ->placeholder('Ingresar email')
                                        ->label('Email de Sucursal')
                                        ->email(true)
                                        ->nullable(),
                                    TextInput::make('branch_phone')
                                        ->label('Teléfono de Sucursal')
                                        ->placeholder('Ingresar teléfono')
                                        ->tel()
                                        ->rule('phone:MX')
                                        ->validationMessages([
                                            'phone' => 'El teléfono no es válido para México.',
                                        ])
                                        ->nullable()
                                ])
                        ]),
                ])
                    ->submitAction(
                        Action::make('submit')
                            ->label('Finalizar')
                            ->action('submit')
                    )
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $data = $this->form->getState();

        try {
            DB::beginTransaction();

            $company = Company::create([
                'name'     => $data['company_name'],
                'email'    => $data['email'] ?? null,
                'phone'    => $data['phone'] ?? null,
                'logo'     => $data['logo'] ?? null,
                'timezone' => 'America/Mexico_City',
                'currency' => 'MXN',
                'country'  => 'MX',
                'status'   => true,
            ]);

            setPermissionsTeamId($company->id);

            app(PermissionRegistrar::class)
                ->setPermissionsTeamId($company->id);

            // Setup inicial de la empresa
            app(CompanySetupService::class)->setupDefaults($company->id);

            $company->users()->attach($user->id);

            // Asignar rol
            $role = Role::where('name', 'owner')
                ->where('company_id', $company->id)
                ->first();

            if (!$role) throw new \Exception('Owner role not found.');

            $user->assignRole($role);

            // Crear sucursal
            $branch = Branch::create([
                'company_id' => $company->id,
                'name'       => $data['branch_name'],
                'address'       => $data['branch_address'],
                'phone'       => $data['branch_phone'],
                'email'       => $data['branch_email'],
                'is_default' => true,
            ]);

            DB::commit();

            session([
                'company_id' => $company->id,
                'branch_id'  => $branch->id,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Onboarding failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw ValidationException::withMessages([
                'general' => 'Ocurrió un error al crear tu empresa.',
            ]);
        }

        $this->redirect(route('filament.admin.pages.dashboard'));
    }
}
