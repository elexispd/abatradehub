<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Password;

class StaffWelcomeNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $loginUrl;
    public string $resetUrl;

    public function __construct(public User $user)
    {
        $this->loginUrl = \App\Support\Admin::url('login');

        $token = Password::createToken($this->user);
        $this->resetUrl = url('/reset-password/'.$token.'?email='.urlencode($this->user->email));
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to AbaTradeHub Staff Portal',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.staff-welcome-notification',
        );
    }
}

