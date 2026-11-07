<?php

namespace App\Livewire;

use App\Mail\NewEnquiryNotification;
use App\Models\Enquiry;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Validate;
use Livewire\Component;

class QuoteForm extends Component
{
    #[Validate('required|string|max:120')]
    public string $name = '';

    #[Validate('required|string|min:7|max:25|regex:/^[0-9+\-\s()]+$/')]
    public string $phone = '';

    #[Validate('required|string|min:5|max:2000')]
    public string $message = '';

    /** Honeypot: real visitors never fill this in. */
    public string $website = '';

    public bool $submitted = false;

    public function submit(): void
    {
        $key = 'quote-form:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('message', 'Too many requests. Please wait a minute or message us on WhatsApp.');

            return;
        }

        $data = $this->validate();
        RateLimiter::hit($key, 60);

        if ($this->website !== '') {
            // Bot: pretend it worked.
            $this->submitted = true;

            return;
        }

        $enquiry = Enquiry::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'message' => $data['message'],
            'source' => 'form',
        ]);

        $adminEmail = config('abatradehub.enquiry_notification_email');
        if ($adminEmail) {
            try {
                Mail::to($adminEmail)->send(new NewEnquiryNotification($enquiry));
            } catch (\Throwable $e) {
                Log::error('Failed sending admin enquiry email: ' . $e->getMessage());
            }
        }

        $this->reset('name', 'phone', 'message');
        $this->submitted = true;
    }

    public function render()
    {
        return view('livewire.quote-form');
    }
}

