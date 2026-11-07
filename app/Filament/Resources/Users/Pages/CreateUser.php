<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Mail\StaffWelcomeNotification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        try {
            Mail::to($this->record->email)->send(new StaffWelcomeNotification($this->record));
        } catch (\Throwable $e) {
            Log::error('Failed sending staff welcome email: '.$e->getMessage());
        }
    }
}
