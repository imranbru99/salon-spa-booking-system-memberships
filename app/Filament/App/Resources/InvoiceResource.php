<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use App\Services\Modules\ModuleService;
use App\Services\PDF\PDFService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Point of Sale & Billing';

    protected static ?string $navigationLabel = 'Invoices & Receipts';

    public static function canViewAny(): bool
    {
        return ModuleService::isEnabled('invoices');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Invoice Information')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('invoice_number')->required(),
                        Forms\Components\DatePicker::make('invoice_date')->required(),
                        Forms\Components\DatePicker::make('due_date')->required(),
                        Forms\Components\TextInput::make('subtotal')->numeric()->prefix('$')->required(),
                        Forms\Components\TextInput::make('tax_amount')->numeric()->prefix('$')->default(0),
                        Forms\Components\TextInput::make('total_amount')->numeric()->prefix('$')->required(),
                        Forms\Components\TextInput::make('paid_amount')->numeric()->prefix('$')->required(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'paid' => 'Paid',
                                'partial' => 'Partial',
                                'issued' => 'Issued',
                                'void' => 'Void',
                            ])
                            ->default('paid'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.full_name')
                    ->label('Client')
                    ->searchable(),
                Tables\Columns\TextColumn::make('invoice_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->money('USD')
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'partial' => 'warning',
                        'void' => 'danger',
                        default => 'info',
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('download_pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(fn (Invoice $record) => app(PDFService::class)->generateInvoicePDF($record)),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageInvoices::route('/'),
        ];
    }
}
