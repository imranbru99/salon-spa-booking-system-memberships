<?php

namespace App\Filament\Staff\Resources;

use App\Filament\Staff\Resources\AppointmentResource\Pages;
use App\Models\Appointment;
use App\Services\Booking\BookingService;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'My Client Appointments';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user && $user->staffProfile) {
            $staffId = $user->staffProfile->id;
            $query->whereHas('services', function ($q) use ($staffId) {
                $q->where('staff_id', $staffId);
            });
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('booking_reference')
                    ->badge()
                    ->color('primary')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer.full_name')
                    ->label('Client')
                    ->searchable()
                    ->weight('bold'),
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
                        'checked_in', 'in_progress' => 'warning',
                        'confirmed' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('customer_notes')
                    ->label('Client Requests')
                    ->limit(35),
            ])
            ->actions([
                Tables\Actions\Action::make('start_service')
                    ->label('Start Service')
                    ->icon('heroicon-o-play')
                    ->color('warning')
                    ->visible(fn (Appointment $record) => in_array($record->status, ['confirmed', 'checked_in']))
                    ->action(fn (Appointment $record) => $record->update(['status' => 'in_progress'])),
                Tables\Actions\Action::make('mark_completed')
                    ->label('Complete')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (Appointment $record) => in_array($record->status, ['in_progress', 'checked_in']))
                    ->action(fn (Appointment $record) => app(BookingService::class)->completeAppointment($record)),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageAppointments::route('/'),
        ];
    }
}
