<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\CustomerResource\Pages;
use App\Models\Customer;
use App\Services\Modules\ModuleService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Clients & CRM';

    public static function canViewAny(): bool
    {
        return ModuleService::isEnabled('customers');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Client Information')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('first_name')->required()->maxLength(100),
                        Forms\Components\TextInput::make('last_name')->maxLength(100),
                        Forms\Components\TextInput::make('email')->email()->maxLength(150),
                        Forms\Components\TextInput::make('phone')->tel()->maxLength(30),
                        Forms\Components\DatePicker::make('date_of_birth'),
                        Forms\Components\Select::make('gender')
                            ->options([
                                'female' => 'Female',
                                'male' => 'Male',
                                'non_binary' => 'Non-binary',
                                'prefer_not_to_say' => 'Prefer not to say',
                            ]),
                    ]),

                Forms\Components\Section::make('CRM & Loyalty')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('segment')
                            ->options([
                                'new' => 'New Client',
                                'returning' => 'Returning',
                                'regular' => 'Regular',
                                'vip' => 'VIP Luminary',
                                'high_value' => 'High Value',
                                'inactive' => 'Inactive',
                            ])
                            ->default('new'),
                        Forms\Components\TextInput::make('loyalty_points')
                            ->numeric()
                            ->default(0),
                        Forms\Components\Textarea::make('notes')->label('Client Notes & Preferences')->columnSpanFull(),
                        Forms\Components\Textarea::make('allergies_treatment_notes')->label('Allergies & Contraindications')->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Client Name')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('segment')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'vip' => 'warning',
                        'returning', 'regular' => 'info',
                        'new' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('loyalty_points')
                    ->label('Points')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('segment')
                    ->options([
                        'vip' => 'VIP',
                        'returning' => 'Returning',
                        'new' => 'New',
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
            'index' => Pages\ManageCustomers::route('/'),
        ];
    }
}
