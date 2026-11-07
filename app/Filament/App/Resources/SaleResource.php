<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\SaleResource\Pages;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Sale;
use App\Services\Modules\ModuleService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Point of Sale & Billing';

    protected static ?string $navigationLabel = 'Sales & Orders';

    public static function canViewAny(): bool
    {
        return ModuleService::isEnabled('pos');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Sale Details')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('sale_number')
                            ->default(fn () => 'POS-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4)))
                            ->required(),
                        Forms\Components\Select::make('branch_id')
                            ->options(Branch::pluck('name', 'id'))
                            ->required(),
                        Forms\Components\Select::make('customer_id')
                            ->options(Customer::all()->pluck('full_name', 'id'))
                            ->searchable(),
                        Forms\Components\TextInput::make('subtotal')->numeric()->prefix('$')->required(),
                        Forms\Components\TextInput::make('tax_amount')->numeric()->prefix('$')->default(0),
                        Forms\Components\TextInput::make('total_amount')->numeric()->prefix('$')->required(),
                        Forms\Components\TextInput::make('paid_amount')->numeric()->prefix('$')->required(),
                        Forms\Components\Select::make('payment_status')
                            ->options([
                                'unpaid' => 'Unpaid',
                                'partially_paid' => 'Partially Paid',
                                'paid' => 'Paid in Full',
                                'refunded' => 'Refunded',
                            ])
                            ->default('paid'),
                        Forms\Components\Select::make('status')
                            ->options([
                                'completed' => 'Completed',
                                'refunded' => 'Refunded',
                                'void' => 'Void',
                            ])
                            ->default('completed'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sale_number')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.full_name')
                    ->label('Customer')
                    ->searchable(),
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Branch')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('total_amount')
                    ->money('USD')
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('payment_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'partially_paid' => 'warning',
                        'refunded' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSales::route('/'),
        ];
    }
}
