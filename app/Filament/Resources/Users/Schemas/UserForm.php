<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Account Details')
                    ->description('Personal identification and contact details.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Full Name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique('users', 'email', ignorable: fn (?User $record) => $record),

                        TextInput::make('phone')
                            ->label('Phone Number')
                            ->tel()
                            ->maxLength(30)
                            ->placeholder('+234 803 000 0000'),
                    ]),

                Section::make('Access & Role Assignment')
                    ->description('Assign security roles and manage account status.')
                    ->columns(2)
                    ->schema([
                        CheckboxList::make('roles')
                            ->label('Assigned Roles')
                            ->relationship('roles', 'name', modifyQueryUsing: function ($query) {
                                // Rule: Only super_admin can see/assign the super_admin role
                                if (! auth()->user()?->isSuperAdmin()) {
                                    $query->where('name', '!=', 'super_admin');
                                }
                            })
                            ->descriptions(fn () => [
                                'super_admin' => 'Full unrestricted access to all panels, roles, and settings.',
                                'Operations' => 'Operational management for products, categories, and orders.',
                            ])
                            ->columns(2)
                            ->columnSpanFull()
                            ->visible(fn () => auth()->user()?->can('assign_roles') ?? false)
                            ->disabled(fn (?User $record) => $record && $record->id === auth()->id())
                            ->helperText(function (?User $record) {
                                if ($record && $record->id === auth()->id()) {
                                    return 'You cannot modify your own roles from this screen.';
                                }

                                return 'Select one or more roles to grant permissions to this staff user.';
                            })
                            ->live(),

                        Toggle::make('is_active')
                            ->label('Active Staff Account')
                            ->default(true)
                            ->helperText('Disabled staff cannot log in to the admin panel. Deactivating immediately terminates active sessions.')
                            ->disabled(function (?User $record) {
                                if (! $record) {
                                    return false;
                                }

                                // Cannot disable oneself
                                if ($record->id === auth()->id()) {
                                    return true;
                                }

                                // Cannot disable last super admin
                                if ($record->isSuperAdmin() && \App\Policies\UserPolicy::isLastSuperAdmin($record)) {
                                    return true;
                                }

                                return false;
                            })
                            ->visible(fn () => auth()->user()?->can('disable_user') ?? false)
                            ->columnSpanFull(),
                    ]),

                Section::make('Security & Password')
                    ->description('Set password credentials and login requirements.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->helperText(fn (string $operation) => $operation === 'edit' ? 'Leave blank to preserve the existing password.' : 'Set a strong password or generate a temporary one.')
                            ->suffixAction(
                                Action::make('generate_password')
                                    ->icon('heroicon-o-sparkles')
                                    ->tooltip('Generate secure temporary password')
                                    ->action(function ($set) {
                                        $temp = Str::password(12, symbols: false);
                                        $set('password', $temp);

                                        Notification::make()
                                            ->title('Temporary Password Generated')
                                            ->body("Generated: <strong>{$temp}</strong><br><small>Copy and securely share with the staff member, or let them set their password via the welcome email.</small>")
                                            ->success()
                                            ->persistent()
                                            ->send();
                                    })
                            ),

                        Checkbox::make('force_password_change')
                            ->label('Force password change at first login')
                            ->default(true)
                            ->columnSpanFull(),
                    ]),

                Section::make('Permissions Summary')
                    ->description('Live preview of all permissions granted by the selected roles.')
                    ->collapsible()
                    ->schema([
                        Placeholder::make('permissions_preview')
                            ->label('')
                            ->content(function ($get, ?User $record) {
                                $selectedRoles = $get('roles') ?? ($record ? $record->roles->pluck('id')->all() : []);

                                if (empty($selectedRoles)) {
                                    return new HtmlString('<div class="text-sm text-gray-500 italic p-3 bg-gray-50 rounded-lg">No roles selected. This user will have no admin permissions.</div>');
                                }

                                $roles = Role::whereIn('id', (array) $selectedRoles)
                                    ->orWhereIn('name', (array) $selectedRoles)
                                    ->with('permissions')
                                    ->get();

                                if ($roles->contains('name', 'super_admin')) {
                                    return new HtmlString('
                                        <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-amber-900 text-sm">
                                            <strong>👑 Super Admin Role Granted:</strong> This user has unrestricted access to all resources, pages, widgets, and settings via Gate::before bypass.
                                        </div>
                                    ');
                                }

                                $permissions = $roles->flatMap->permissions->unique('name');

                                if ($permissions->isEmpty()) {
                                    return new HtmlString('<div class="text-sm text-gray-500 italic p-3 bg-gray-50 rounded-lg">The selected role(s) currently carry no direct permissions.</div>');
                                }

                                $badgeList = $permissions->map(function ($perm) {
                                    return '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200 mr-1.5 mb-1.5">'.$perm->name.'</span>';
                                })->join('');

                                return new HtmlString('<div class="p-3 bg-white border border-gray-100 rounded-lg"><div class="text-xs font-semibold text-gray-700 mb-2">Granted Permissions ('.count($permissions).'):</div><div class="flex flex-wrap">'.$badgeList.'</div></div>');
                            }),
                    ]),
            ]);
    }
}
