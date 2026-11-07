<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use App\Policies\UserPolicy;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Password;
use Spatie\Permission\Models\Role;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (User $record) => $record->phone),

                TextColumn::make('email')
                    ->searchable()
                    ->copyable()
                    ->sortable(),

                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'super_admin' => 'warning',
                        default => 'primary',
                    })
                    ->separator(','),

                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->state(fn (User $record): string => $record->is_active ? 'Active' : 'Disabled')
                    ->color(fn (string $state): string => match ($state) {
                        'Active' => 'success',
                        default => 'danger',
                    }),

                TextColumn::make('last_login_at')
                    ->label('Last Login')
                    ->since()
                    ->placeholder('Never')
                    ->dateTimeTooltip()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->since()
                    ->dateTimeTooltip()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->preload(),

                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->trueLabel('Active Staff')
                    ->falseLabel('Disabled Staff'),

                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),

                EditAction::make()
                    ->visible(fn (User $record) => auth()->user()?->can('update', $record) ?? false),

                Action::make('toggle_active')
                    ->label(fn (User $record) => $record->is_active ? 'Disable' : 'Enable')
                    ->icon(fn (User $record) => $record->is_active ? 'heroicon-o-no-symbol' : 'heroicon-o-check-circle')
                    ->color(fn (User $record) => $record->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->modalHeading(fn (User $record) => $record->is_active ? 'Deactivate Staff User' : 'Activate Staff User')
                    ->modalDescription(fn (User $record) => $record->is_active
                        ? 'Deactivating this staff member will immediately terminate all their active sessions and prevent panel login.'
                        : 'Activating this staff member will allow them to access the admin panel.')
                    ->visible(fn (User $record) => auth()->user()?->can('disable', $record) ?? false)
                    ->action(function (User $record) {
                        $newStatus = ! $record->is_active;
                        $record->update(['is_active' => $newStatus]);

                        if (! $newStatus) {
                            $record->endAllSessions();
                        }

                        Notification::make()
                            ->title($newStatus ? 'Staff user activated' : 'Staff user deactivated')
                            ->success()
                            ->send();
                    }),

                Action::make('send_password_reset')
                    ->label('Send Reset Link')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Send Password Reset Link')
                    ->modalDescription(fn (User $record) => "Send an official password reset email to {$record->email}?")
                    ->visible(fn (User $record) => auth()->user()?->can('resetPassword', $record) ?? false)
                    ->action(function (User $record) {
                        Password::sendResetLink(['email' => $record->email]);

                        Notification::make()
                            ->title('Password reset link sent to '.$record->email)
                            ->success()
                            ->send();
                    }),

                DeleteAction::make()
                    ->visible(fn (User $record) => auth()->user()?->can('delete', $record) ?? false),

                RestoreAction::make()
                    ->visible(fn (User $record) => auth()->user()?->can('restore', $record) ?? false),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('bulk_disable')
                        ->label('Disable selected')
                        ->icon('heroicon-o-no-symbol')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible(fn () => auth()->user()?->can('disable_user') ?? false)
                        ->action(function (Collection $records) {
                            $currentId = auth()->id();
                            $disabledCount = 0;

                            $records->each(function (User $user) use ($currentId, &$disabledCount) {
                                if ($user->id === $currentId) {
                                    return;
                                }

                                if ($user->isSuperAdmin() && UserPolicy::isLastSuperAdmin($user)) {
                                    return;
                                }

                                $user->update(['is_active' => false]);
                                $user->endAllSessions();
                                $disabledCount++;
                            });

                            Notification::make()
                                ->title("{$disabledCount} staff member(s) deactivated")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('bulk_enable')
                        ->label('Enable selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn () => auth()->user()?->can('disable_user') ?? false)
                        ->action(function (Collection $records) {
                            $records->each->update(['is_active' => true]);

                            Notification::make()
                                ->title("{$records->count()} staff member(s) activated")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('bulk_assign_role')
                        ->label('Assign role')
                        ->icon('heroicon-o-shield-check')
                        ->color('primary')
                        ->visible(fn () => auth()->user()?->can('assign_roles') ?? false)
                        ->schema([
                            Select::make('role_name')
                                ->label('Select Role')
                                ->options(function () {
                                    if (auth()->user()?->isSuperAdmin()) {
                                        return Role::pluck('name', 'name');
                                    }

                                    return Role::where('name', '!=', 'super_admin')->pluck('name', 'name');
                                })
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $roleName = $data['role_name'];
                            $role = Role::where('name', $roleName)->firstOrFail();

                            // Prevent non-super admin from assigning super admin
                            if ($roleName === 'super_admin' && ! auth()->user()?->isSuperAdmin()) {
                                abort(403);
                            }

                            $records->each(function (User $user) use ($role) {
                                if (! $user->hasRole($role)) {
                                    $user->assignRole($role);
                                }
                            });

                            Notification::make()
                                ->title("Role [{$roleName}] assigned to {$records->count()} user(s)")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->can('delete_user') ?? false),

                    RestoreBulkAction::make()
                        ->visible(fn () => auth()->user()?->can('restore_user') ?? false),
                ]),
            ]);
    }
}
