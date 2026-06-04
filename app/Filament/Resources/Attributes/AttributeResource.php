<?php

namespace App\Filament\Resources\Attributes;

use App\Filament\Resources\Attributes\Pages\CreateAttribute;
use App\Filament\Resources\Attributes\Pages\EditAttribute;
use App\Filament\Resources\Attributes\Pages\ListAttributes;
use App\Filament\Resources\Attributes\Pages\ViewAttribute;
use App\Filament\Resources\Attributes\Schemas\AttributeForm;
use App\Filament\Resources\Attributes\Schemas\AttributeInfolist;
use App\Filament\Resources\Attributes\Tables\AttributesTable;
use App\Models\Attribute;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Enums\NavigationGroup;
use BackedEnum;
use UnitEnum;

class AttributeResource extends Resource
{
    protected static ?string $model = Attribute::class;

    protected static string | UnitEnum | null $navigationGroup = NavigationGroup::Settings;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::PaintBrush;

    public static function form(Schema $schema): Schema
    {
        return AttributeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AttributeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AttributesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAttributes::route('/'),
            'create' => CreateAttribute::route('/create'),
            'view' => ViewAttribute::route('/{record}'),
            'edit' => EditAttribute::route('/{record}/edit'),
        ];
    }

    // Translations
    public static function getNavigationLabel(): string
    {
        return __('models.attribute.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('models.attribute.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('models.attribute.plural');
    }
}
