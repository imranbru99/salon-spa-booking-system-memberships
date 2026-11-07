<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TenantResource\Pages;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Platform Tenancy';

    protected static ?string $navigationLabel = 'Businesses & Tenants';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Business Identity')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\Select::make('plan_id')
                            ->label('Subscription Plan')
                            ->options(SubscriptionPlan::pluck('name', 'id'))
                            ->searchable(),
                        Forms\Components\Select::make('business_type')
                            ->options([
                                'salon' => 'Independent Hair & Beauty Salon',
                                'luxury_spa' => 'Luxury Day Spa & Wellness Center',
                                'barbershop' => 'Gentlemen\'s Barbershop',
                                'nail_studio' => 'Nail & Lash Studio',
                                'wellness_centre' => 'Holistic Health & Wellness Center',
                                'chain' => 'Multi-location Salon Chain',
                            ])
                            ->default('salon')
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'trial' => 'Trial Period',
                                'suspended' => 'Suspended',
                                'expired' => 'Expired',
                            ])
                            ->default('active')
                            ->required(),
                        Forms\Components\Toggle::make('is_demo')
                            ->label('Demo Business Account')
                            ->default(false),
                    ]),

                Forms\Components\Section::make('Contact & Localization')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('email')->email(),
                        Forms\Components\TextInput::make('phone'),
                        Forms\Components\TextInput::make('city'),
                        Forms\Components\TextInput::make('currency')->default('USD')->maxLength(10),
                        Forms\Components\TextInput::make('timezone')->default('America/New_York'),
                        Forms\Components\TextInput::make('locale')->default('en')->maxLength(10),
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
                Tables\Columns\TextColumn::make('slug')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('plan.name')
                    ->label('Plan')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('business_type')
                    ->formatStateUsing(fn (string $state): string => ucwords(str_replace('_', ' ', $state)))
                    ->badge()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'trial' => 'warning',
                        'suspended', 'expired' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_demo')
                    ->boolean()
                    ->label('Demo'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'trial' => 'Trial',
                        'suspended' => 'Suspended',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageTenants::route('/'),
        ];
    }
}
