<?php

namespace App\Filament\Reception\Resources;

use App\Filament\Reception\Resources\AppointmentResource\Pages;
use App\Models\Appointment;
use App\Models\Branch;
use App\Models\Customer;
use App\Services\Booking\BookingService;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Today\'s Schedule & Check-in';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('branch_id')
                    ->options(Branch::pluck('name', 'id'))
                    ->required(),
                Forms\Components\Select::make('customer_id')
                    ->options(Customer::all()->pluck('full_name', 'id'))
                    ->required()
                    ->searchable(),
                Forms\Components\DatePicker::make('appointment_date')->default(now()->toDateString())->required(),
                Forms\Components\TimePicker::make('start_time')->required(),
                Forms\Components\TimePicker::make('end_time')->required(),
                Forms\Components\TextInput::make('total_amount')->numeric()->prefix('$')->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'confirmed' => 'Confirmed',
                        'checked_in' => 'Checked In',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                        'no_show' => 'No Show',
                    ])
                    ->default('checked_in'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('start_time')
                    ->label('Time')
                    ->time('g:i A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('booking_reference')
                    ->badge()
                    ->color('primary')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer.full_name')
                    ->label('Client')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('customer.phone')
                    ->label('Phone'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'checked_in' => 'warning',
                        'confirmed' => 'info',
                        'cancelled', 'no_show' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('payment_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'partially_paid' => 'warning',
                        default => 'danger',
                    }),
                Tables\Columns\TextColumn::make('total_amount')
                    ->money('USD'),
            ])
            ->filters([
                Tables\Filters\Filter::make('today_only')
                    ->label('Today\'s Appointments')
                    ->default()
                    ->query(fn ($query) => $query->whereDate('appointment_date', Carbon::today())),
            ])
            ->actions([
                Tables\Actions\Action::make('check_in')
                    ->label('Check In')
                    ->icon('heroicon-o-check-circle')
                    ->color('warning')
                    ->visible(fn (Appointment $record) => in_array($record->status, ['confirmed', 'pending']))
                    ->action(fn (Appointment $record) => $record->update(['status' => 'checked_in'])),
                Tables\Actions\Action::make('complete')
                    ->label('Checkout & Complete')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (Appointment $record) => in_array($record->status, ['checked_in', 'in_progress', 'confirmed']))
                    ->action(fn (Appointment $record) => app(BookingService::class)->completeAppointment($record)),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageAppointments::route('/'),
        ];
    }
}
