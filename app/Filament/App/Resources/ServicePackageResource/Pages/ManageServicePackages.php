<?php

namespace App\Filament\App\Resources\ServicePackageResource\Pages;

use App\Filament\App\Resources\ServicePackageResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageServicePackages extends ManageRecords
{
    protected static string $resource = ServicePackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
