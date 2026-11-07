<?php

namespace App\Filament\App\Resources\StaffProfileResource\Pages;

use App\Filament\App\Resources\StaffProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageStaffProfiles extends ManageRecords
{
    protected static string $resource = StaffProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
