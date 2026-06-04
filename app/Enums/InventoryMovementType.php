<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum InventoryMovementType: string implements HasLabel
{
    case Purchase = 'purchase';
    case Sale = 'sale';
    case Adjustment = 'adjustment';
    case TransferIn = 'transfer_in';
    case TransferOut = 'transfer_out';
    case ReturnSale = 'return_sale';
    case ReturnPurchase = 'return_purchase';
    case Damage = 'damage';
    case Expired = 'expired';
    case InitialStock = 'initial_stock';

    public function getLabel(): string
    {
        return match ($this) {
            self::Purchase => 'Compra',
            self::Sale => 'Venta',
            self::Adjustment => 'Ajuste',
            self::TransferIn => 'Transferencia entrada',
            self::TransferOut => 'Transferencia salida',
            self::ReturnSale => 'Devolución venta',
            self::ReturnPurchase => 'Devolución compra',
            self::Damage => 'Merma',
            self::Expired => 'Expirado',
            self::InitialStock => 'Stock inicial',
        };
    }
}
