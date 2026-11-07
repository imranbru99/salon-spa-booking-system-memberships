<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\StaffProfileResource\Pages;
use App\Models\Branch;
use App\Models\StaffProfile;
use App\Models\User;
use App\Services\Modules\ModuleService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StaffProfileResource extends Resource
{
    protected static ?string $model = StaffProfile::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Services & Staff';

    protected static ?string $navigationLabel = 'Staff & Employees';

    public static function canViewAny(): bool
    {
        return ModuleService::isEnabled('staff');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Profile Information')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('User Account')
                            ->options(User::pluck('name', 'id'))
                            ->required()
                            ->searchable(),
                        Forms\Components\Select::make('branch_id')
                            ->label('Primary Branch')
                            ->options(Branch::pluck('name', 'id'))
                            ->searchable(),
                        Forms\Components\TextInput::make('employee_code')
                            ->required()
                            ->maxLength(30),
                        Forms\Components\TextInput::make('job_title')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\Textarea::make('bio')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Compensation & Employment')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('commission_rate')
                            ->numeric()
                            ->suffix('%')
                            ->default(20.0),
                        Forms\Components\TextInput::make('base_salary')
                            ->numeric()
                            ->prefix('$')
                            ->default(0),
                        Forms\Components\Select::make('employment_type')
                            ->options([
                                'full_time' => 'Full Time',
                                'part_time' => 'Part Time',
                                'contractor' => 'Independent Contractor',
                            ])
                            ->default('full_time'),
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'on_leave' => 'On Leave',
                                'inactive' => 'Inactive',
                            ])
                            ->default('active'),
                        Forms\Components\DatePicker::make('joining_date'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee_code')
                    ->badge()
                    ->color('gray')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Staff Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('job_title')
                    ->badge()
                    ->color('info')
                    ->searchable(),
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Branch')
                    ->sortable(),
                Tables\Columns\TextColumn::make('commission_rate')
                    ->label('Commission')
                    ->formatStateUsing(fn ($state) => "{$state}%"),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'on_leave' => 'warning',
                        default => 'danger',
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageStaffProfiles::route('/'),
        ];
    }
}
