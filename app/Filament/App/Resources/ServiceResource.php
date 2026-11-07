<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\ServiceResource\Pages;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Services\Modules\ModuleService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationGroup = 'Services & Staff';

    public static function canViewAny(): bool
    {
        return ModuleService::isEnabled('services');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Treatment Overview')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('category_id')
                            ->label('Category')
                            ->options(ServiceCategory::pluck('name', 'id'))
                            ->searchable(),
                        Forms\Components\Textarea::make('description')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Pricing & Scheduling')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                        Forms\Components\TextInput::make('sale_price')
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\TextInput::make('duration_minutes')
                            ->numeric()
                            ->suffix('min')
                            ->default(60)
                            ->required(),
                        Forms\Components\TextInput::make('buffer_before')
                            ->numeric()
                            ->suffix('min')
                            ->default(0),
                        Forms\Components\TextInput::make('buffer_after')
                            ->numeric()
                            ->suffix('min')
                            ->default(5),
                        Forms\Components\TextInput::make('commission_rate')
                            ->numeric()
                            ->suffix('%')
                            ->default(20.0),
                    ]),

                Forms\Components\Section::make('Deposits & Status')
                    ->columns(3)
                    ->schema([
                        Forms\Components\Toggle::make('deposit_required')->label('Require Deposit'),
                        Forms\Components\TextInput::make('deposit_amount')->numeric()->prefix('$')->default(0),
                        Forms\Components\Toggle::make('is_active')->label('Available for Booking')->default(true),
                        Forms\Components\Toggle::make('is_featured')->label('Featured on Website')->default(false),
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
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('duration_minutes')
                    ->label('Duration')
                    ->formatStateUsing(fn ($state) => "{$state} min")
                    ->sortable(),
                Tables\Columns\TextColumn::make('commission_rate')
                    ->label('Commission')
                    ->formatStateUsing(fn ($state) => "{$state}%"),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean()
                    ->label('Featured'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageServices::route('/'),
        ];
    }
}
