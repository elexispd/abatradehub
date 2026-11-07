<?php

namespace App\Filament\Resources\AuditLogs\Tables;

use App\Models\AuditLog;
use App\Models\User;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class AuditLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Timestamp')
                    ->since()
                    ->dateTimeTooltip()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('user_name')
                    ->label('Staff Member')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn (AuditLog $record) => $record->user_email),

                TextColumn::make('action')
                    ->label('Event')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'login' => 'info',
                        'logout' => 'gray',
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        'restored' => 'primary',
                        'status_changed' => 'primary',
                        'enabled' => 'success',
                        'disabled' => 'danger',
                        default => 'secondary',
                    })
                    ->sortable(),

                TextColumn::make('auditable_type')
                    ->label('Entity')
                    ->formatStateUsing(fn (?string $state) => $state ? class_basename($state) : 'Auth / System')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('description')
                    ->label('Action Details')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('action')
                    ->label('Event Type')
                    ->options([
                        'login' => 'Login',
                        'logout' => 'Logout',
                        'created' => 'Created Record',
                        'updated' => 'Updated Record',
                        'status_changed' => 'Status Changed',
                        'deleted' => 'Deleted Record',
                        'restored' => 'Restored Record',
                        'enabled' => 'Activated User',
                        'disabled' => 'Deactivated User',
                    ]),

                SelectFilter::make('user_id')
                    ->label('Staff Member')
                    ->options(fn () => User::pluck('name', 'id')),

                SelectFilter::make('auditable_type')
                    ->label('Target Entity')
                    ->options([
                        'App\Models\Product' => 'Products',
                        'App\Models\Enquiry' => 'Enquiries',
                        'App\Models\User' => 'Staff Users',
                        'App\Models\Category' => 'Categories',
                        'App\Models\Supplier' => 'Suppliers',
                    ]),
            ])
            ->recordActions([
                ViewAction::make()
                    ->modalHeading(fn (AuditLog $record) => "Audit Log #{$record->id} Details")
                    ->modalWidth('4xl')
                    ->schema([
                        Section::make('Event Summary')
                            ->columns(2)
                            ->schema([
                                TextEntry::make('created_at')->label('Timestamp')->dateTime(),
                                TextEntry::make('user_name')->label('Staff Member')->helperText(fn (AuditLog $record) => $record->user_email),
                                TextEntry::make('action')->label('Event Type')->badge(),
                                TextEntry::make('auditable_type')->label('Target Entity')->formatStateUsing(fn (?string $state) => $state ? class_basename($state) : 'System'),
                                TextEntry::make('description')->label('Action Summary')->columnSpanFull(),
                                TextEntry::make('ip_address')->label('IP Address')->copyable(),
                                TextEntry::make('user_agent')->label('Browser / Device Agent')->columnSpanFull()->wrap(),
                            ]),

                        Section::make('Attribute Changes (Diff)')
                            ->visible(fn (AuditLog $record) => ! empty($record->old_values) || ! empty($record->new_values))
                            ->schema([
                                TextEntry::make('diff_preview')
                                    ->label('')
                                    ->columnSpanFull()
                                    ->state(function (AuditLog $record) {
                                        $old = $record->old_values ?? [];
                                        $new = $record->new_values ?? [];
                                        $allKeys = array_unique(array_merge(array_keys($old), array_keys($new)));

                                        $rows = '';
                                        foreach ($allKeys as $key) {
                                            $oldVal = isset($old[$key]) ? (is_array($old[$key]) ? json_encode($old[$key]) : (string) $old[$key]) : 'None';
                                            $newVal = isset($new[$key]) ? (is_array($new[$key]) ? json_encode($new[$key]) : (string) $new[$key]) : 'None';

                                            $rows .= '
                                                <tr class="border-b border-gray-100">
                                                    <td class="py-2 px-3 font-semibold text-xs text-gray-700 bg-gray-50">'.e($key).'</td>
                                                    <td class="py-2 px-3 text-xs text-red-700 bg-red-50/50">'.e($oldVal).'</td>
                                                    <td class="py-2 px-3 text-xs text-green-700 bg-green-50/50">'.e($newVal).'</td>
                                                </tr>
                                            ';
                                        }

                                        return new HtmlString('
                                            <div class="overflow-x-auto rounded-lg border border-gray-200">
                                                <table class="w-full text-left border-collapse">
                                                    <thead>
                                                        <tr class="bg-gray-100 text-xs text-gray-600 uppercase font-semibold">
                                                            <th class="py-2 px-3">Field</th>
                                                            <th class="py-2 px-3">Previous Value</th>
                                                            <th class="py-2 px-3">New Value</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>'.$rows.'</tbody>
                                                </table>
                                            </div>
                                        ');
                                    }),
                            ]),
                    ]),
            ]);
    }
}
