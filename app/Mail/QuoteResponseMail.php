<?php
namespace App\Mail;
use App\Models\QuoteRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
class QuoteResponseMail extends Mailable {
 use Queueable, SerializesModels;
 public function __construct(public QuoteRequest $quoteRequest, public string $mailSubject, public string $mailBody, public ?\App\Models\QuoteMessage $quoteMessage=null) {}
 public function envelope(): Envelope { return new Envelope(subject:$this->mailSubject); }
 public function content(): Content { return new Content(view:'emails.quote-response'); }
 public function attachments(): array { return $this->quoteMessage?->shouldAttachToEmail()?[Attachment::fromStorageDisk('local',$this->quoteMessage->attachment_path)->as($this->quoteMessage->attachment_name)->withMime($this->quoteMessage->attachment_mime)]:[]; }
}
