<?php

namespace App\Filament\Resources\PriceLists;

use App\Enums\NavigationGroup;
use App\Filament\Resources\PriceLists\Pages\CreatePriceList;
use App\Filament\Resources\PriceLists\Pages\EditPriceList;
use App\Filament\Resources\PriceLists\Pages\ListPriceLists;
use App\Filament\Resources\PriceLists\Pages\ViewPriceList;
use App\Filament\Resources\PriceLists\Schemas\PriceListForm;
use App\Filament\Resources\PriceLists\Schemas\PriceListInfolist;
use App\Filament\Resources\PriceLists\Tables\PriceListsTable;
use App\Models\PriceList;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PriceListResource extends Resource
{
    protected static ?string $model = PriceList::class;

    protected static string | UnitEnum | null $navigationGroup = NavigationGroup::Settings;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ListBullet;

    public static function form(Schema $schema): Schema
    {
        return PriceListForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PriceListInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PriceListsTable::configure($table);
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
            'index' => ListPriceLists::route('/'),
            'create' => CreatePriceList::route('/create'),
            'view' => ViewPriceList::route('/{record}'),
            'edit' => EditPriceList::route('/{record}/edit'),
        ];
    }

    // Translations
    public static function getNavigationLabel(): string
    {
        return __('models.price_list.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('models.price_list.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('models.price_list.plural');
    }
}
