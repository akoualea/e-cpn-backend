<?php
namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;

class ProAccountRejected extends Mailable {
    use Queueable, SerializesModels;
    public $user;
    public function __construct($user) { $this->user = $user; }
    public function content(): Content { return new Content(markdown: 'emails.pros.rejected'); }
}