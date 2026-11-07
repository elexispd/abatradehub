<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->visible(fn () => auth()->user()?->can('delete', $this->record) ?? false),
            ForceDeleteAction::make()
                ->visible(fn () => auth()->user()?->can('forceDelete', $this->record) ?? false),
            RestoreAction::make()
                ->visible(fn () => auth()->user()?->can('restore', $this->record) ?? false),
        ];
    }
}
