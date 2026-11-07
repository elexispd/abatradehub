<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Staff Profile Details')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Full Name')
                            ->weight('bold'),

                        TextEntry::make('email')
                            ->label('Email Address')
                            ->copyable(),

                        TextEntry::make('phone')
                            ->label('Phone Number')
                            ->placeholder('Not specified')
                            ->copyable(),

                        TextEntry::make('is_active')
                            ->label('Account Status')
                            ->badge()
                            ->state(fn (User $record): string => $record->is_active ? 'Active' : 'Disabled')
                            ->color(fn (string $state): string => match ($state) {
                                'Active' => 'success',
                                default => 'danger',
                            }),
                    ]),

                Section::make('Access & Role Assignments')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('roles.name')
                            ->label('Assigned Roles')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'super_admin' => 'warning',
                                default => 'primary',
                            })
                            ->separator(','),

                        TextEntry::make('last_login_at')
                            ->label('Last Recorded Login')
                            ->since()
                            ->placeholder('Never logged in')
                            ->dateTimeTooltip(),

                        TextEntry::make('creator.name')
                            ->label('Created By')
                            ->helperText(fn (User $record) => $record->creator?->email)
                            ->placeholder('System Initial Setup'),

                        TextEntry::make('created_at')
                            ->label('Account Created')
                            ->dateTime(),
                    ]),
            ]);
    }
}
