<?php

namespace App\Filament\App\Resources\MembershipPlanResource\Pages;

use App\Filament\App\Resources\MembershipPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageMembershipPlans extends ManageRecords
{
    protected static string $resource = MembershipPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
