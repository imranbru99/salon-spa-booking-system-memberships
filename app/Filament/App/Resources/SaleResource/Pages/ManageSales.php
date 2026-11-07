<?php

namespace App\Filament\App\Resources\SaleResource\Pages;

use App\Filament\App\Resources\SaleResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageSales extends ManageRecords
{
    protected static string $resource = SaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
