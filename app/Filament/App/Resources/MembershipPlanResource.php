<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\MembershipPlanResource\Pages;
use App\Models\MembershipPlan;
use App\Services\Modules\ModuleService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MembershipPlanResource extends Resource
{
    protected static ?string $model = MembershipPlan::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationGroup = 'Packages & Memberships';

    protected static ?string $navigationLabel = 'Membership Plans';

    public static function canViewAny(): bool
    {
        return ModuleService::isEnabled('memberships');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Membership Plan Details')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('price')
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                        Forms\Components\Select::make('billing_frequency')
                            ->options([
                                'monthly' => 'Monthly',
                                'quarterly' => 'Quarterly',
                                'annual' => 'Annual',
                                'lifetime' => 'Lifetime',
                            ])
                            ->default('monthly')
                            ->required(),
                        Forms\Components\TextInput::make('discount_percentage')
                            ->numeric()
                            ->suffix('%')
                            ->default(15.0),
                        Forms\Components\TextInput::make('free_service_credits')
                            ->label('Complimentary Service Credits')
                            ->numeric()
                            ->default(1),
                        Forms\Components\Toggle::make('priority_booking')
                            ->label('Priority Booking Status')
                            ->default(true),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Available for Signups')
                            ->default(true),
                        Forms\Components\Textarea::make('description')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('billing_frequency')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
                Tables\Columns\TextColumn::make('discount_percentage')
                    ->label('Discount')
                    ->formatStateUsing(fn ($state) => "{$state}%"),
                Tables\Columns\TextColumn::make('free_service_credits')
                    ->label('Free Credits')
                    ->numeric(),
                Tables\Columns\IconColumn::make('priority_booking')
                    ->boolean()
                    ->label('Priority'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageMembershipPlans::route('/'),
        ];
    }
}
