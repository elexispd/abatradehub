<?php

namespace App\Livewire;

use App\Mail\NewEnquiryNotification;
use App\Models\Enquiry;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class CustomPhotoRequest extends Component
{
    use WithFileUploads;

    public bool $isOpen = false;

    #[Validate('required|string|min:7|max:25|regex:/^[0-9+\-\s()]+$/')]
    public string $phone = '';

    #[Validate('nullable|string|max:500')]
    public string $notes = '';

    #[Validate('required|string|max:100')]
    public string $country = 'Nigeria';

    #[Validate(['photos' => 'required', 'photos.*' => 'image|max:5120'])]
    public array $photos = [];

    /** Honeypot */
    public string $website = '';

    public bool $submitted = false;

    #[On('open-custom-photo-request')]
    public function open(): void
    {
        $this->reset('phone', 'notes', 'photos', 'website', 'submitted');
        $this->resetValidation();
        $this->isOpen = true;
    }

    public function close(): void
    {
        $this->isOpen = false;
    }

    public function removePhoto(int $index): void
    {
        array_splice($this->photos, $index, 1);
    }

    public function submit(): void
    {
        $key = 'custom-photo:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('phone', 'Too many requests. Please wait a minute or message us on WhatsApp.');
            return;
        }

        $this->validate();
        RateLimiter::hit($key, 60);

        if ($this->website !== '') {
            $this->submitted = true;
            return;
        }

        $enquiry = Enquiry::create([
            'name'    => 'Photo Request',
            'phone'   => $this->phone,
            'country' => $this->country,
            'message' => $this->notes ?: null,
            'source'  => 'custom_photo',
        ]);

        foreach ($this->photos as $photo) {
            $enquiry->addMedia($photo->getRealPath())
                ->usingFileName($photo->getClientOriginalName())
                ->toMediaCollection('reference_photos');
        }

        $adminEmail = config('abatradehub.enquiry_notification_email');
        if ($adminEmail) {
            try {
                Mail::to($adminEmail)->send(new NewEnquiryNotification($enquiry));
            } catch (\Throwable $e) {
                Log::error('Failed sending admin enquiry email: '.$e->getMessage());
            }
        }

        $this->submitted = true;
    }

    public function render()
    {
        return view('livewire.custom-photo-request');
    }
}
