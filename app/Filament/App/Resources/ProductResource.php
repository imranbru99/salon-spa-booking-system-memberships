<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Supplier;
use App\Services\Modules\ModuleService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationGroup = 'Inventory & Retail';

    protected static ?string $navigationLabel = 'Products & Stock';

    public static function canViewAny(): bool
    {
        return ModuleService::isEnabled('inventory');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Product Information')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')->required()->maxLength(255),
                        Forms\Components\TextInput::make('sku')->required()->maxLength(50),
                        Forms\Components\TextInput::make('barcode')->maxLength(50),
                        Forms\Components\TextInput::make('brand')->maxLength(100),
                        Forms\Components\Select::make('category_id')
                            ->label('Category')
                            ->options(ProductCategory::pluck('name', 'id'))
                            ->searchable(),
                        Forms\Components\Select::make('supplier_id')
                            ->label('Supplier')
                            ->options(Supplier::pluck('name', 'id'))
                            ->searchable(),
                        Forms\Components\Textarea::make('description')->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Pricing & Stock Inventory')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('purchase_price')->numeric()->prefix('$')->default(0),
                        Forms\Components\TextInput::make('selling_price')->numeric()->prefix('$')->required(),
                        Forms\Components\TextInput::make('tax_percent')->numeric()->suffix('%')->default(8.5),
                        Forms\Components\TextInput::make('stock_quantity')->numeric()->default(0)->required(),
                        Forms\Components\TextInput::make('min_stock_alert')->numeric()->default(10)->required(),
                        Forms\Components\Toggle::make('is_active')->label('Available for Retail')->default(true),
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
                Tables\Columns\TextColumn::make('sku')
                    ->badge()
                    ->color('gray')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('selling_price')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('In Stock')
                    ->badge()
                    ->color(fn (Product $record): string => $record->isLowStock() ? 'danger' : 'success')
                    ->sortable(),
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
            'index' => Pages\ManageProducts::route('/'),
        ];
    }
}
