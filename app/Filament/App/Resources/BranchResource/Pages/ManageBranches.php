<?php

namespace App\Filament\App\Resources\BranchResource\Pages;

use App\Filament\App\Resources\BranchResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageBranches extends ManageRecords
{
    protected static string $resource = BranchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
