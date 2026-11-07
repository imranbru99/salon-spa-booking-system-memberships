<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\AppointmentResource\Pages;
use App\Models\Appointment;
use App\Models\Branch;
use App\Models\Customer;
use App\Services\Modules\ModuleService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Appointments & Bookings';

    public static function canViewAny(): bool
    {
        return ModuleService::isEnabled('booking');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Booking Details')
                    ->columns(3)
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->label('Branch')
                            ->options(Branch::pluck('name', 'id'))
                            ->required()
                            ->searchable(),
                        Forms\Components\Select::make('customer_id')
                            ->label('Customer')
                            ->options(Customer::all()->pluck('full_name', 'id'))
                            ->required()
                            ->searchable(),
                        Forms\Components\TextInput::make('booking_reference')
                            ->default(fn () => 'BK-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4)))
                            ->required()
                            ->maxLength(30),
                        Forms\Components\DatePicker::make('appointment_date')
                            ->required()
                            ->default(now()->toDateString()),
                        Forms\Components\TimePicker::make('start_time')
                            ->required()
                            ->default('10:00:00'),
                        Forms\Components\TimePicker::make('end_time')
                            ->required()
                            ->default('11:00:00'),
                    ]),

                Forms\Components\Section::make('Financials & Status')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('total_amount')
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                        Forms\Components\TextInput::make('paid_amount')
                            ->numeric()
                            ->prefix('$')
                            ->default(0),
                        Forms\Components\TextInput::make('deposit_amount')
                            ->numeric()
                            ->prefix('$')
                            ->default(0),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'confirmed' => 'Confirmed',
                                'checked_in' => 'Checked In',
                                'in_progress' => 'In Progress',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                                'no_show' => 'No Show',
                                'rescheduled' => 'Rescheduled',
                            ])
                            ->default('confirmed')
                            ->required(),
                        Forms\Components\Select::make('payment_status')
                            ->options([
                                'unpaid' => 'Unpaid',
                                'partially_paid' => 'Partially Paid',
                                'paid' => 'Paid in Full',
                                'refunded' => 'Refunded',
                            ])
                            ->default('unpaid')
                            ->required(),
                        Forms\Components\Select::make('channel')
                            ->options([
                                'online' => 'Online Booking',
                                'receptionist' => 'Front Desk / Phone',
                                'walk_in' => 'Walk-in Client',
                                'ai_assistant' => 'AI Booking Concierge',
                            ])
                            ->default('online'),
                    ]),

                Forms\Components\Section::make('Client Notes')
                    ->schema([
                        Forms\Components\Textarea::make('customer_notes')->label('Client Preferences / Requests'),
                        Forms\Components\Textarea::make('internal_notes')->label('Internal Staff Notes'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('booking_reference')
                    ->searchable()
                    ->badge()
                    ->color('primary')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('customer.full_name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Branch')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('appointment_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_time')
                    ->label('Time')
                    ->time('g:i A'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'confirmed' => 'info',
                        'checked_in', 'in_progress' => 'warning',
                        'cancelled', 'no_show' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('payment_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'partially_paid' => 'warning',
                        'refunded' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('total_amount')
                    ->money('USD')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'confirmed' => 'Confirmed',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                        'checked_in' => 'Checked In',
                    ]),
                Tables\Filters\SelectFilter::make('branch_id')
                    ->label('Branch')
                    ->options(Branch::pluck('name', 'id')),
            ])
            ->actions([
                Tables\Actions\Action::make('check_in')
                    ->label('Check In')
                    ->icon('heroicon-o-check-circle')
                    ->color('warning')
                    ->visible(fn (Appointment $record) => in_array($record->status, ['confirmed', 'pending']))
                    ->action(fn (Appointment $record) => $record->update(['status' => 'checked_in'])),
                Tables\Actions\Action::make('complete')
                    ->label('Complete')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (Appointment $record) => in_array($record->status, ['checked_in', 'in_progress', 'confirmed']))
                    ->action(fn (Appointment $record) => app(\App\Services\Booking\BookingService::class)->completeAppointment($record)),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageAppointments::route('/'),
        ];
    }
}
