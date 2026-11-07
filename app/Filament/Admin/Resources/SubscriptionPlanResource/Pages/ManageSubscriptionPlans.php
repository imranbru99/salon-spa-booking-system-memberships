<?php

namespace App\Filament\Admin\Resources\SubscriptionPlanResource\Pages;

use App\Filament\Admin\Resources\SubscriptionPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageSubscriptionPlans extends ManageRecords
{
    protected static string $resource = SubscriptionPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
