<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\URL;
class QuoteMessage extends Model {
 protected $fillable=['quote_request_id','user_id','recipient','subject','body','attachment_name','attachment_path','attachment_mime','attachment_size','sent_at'];
 protected function casts(): array { return ['attachment_size'=>'integer','sent_at'=>'datetime']; }
 public function quoteRequest(): BelongsTo { return $this->belongsTo(QuoteRequest::class); }
 public function user(): BelongsTo { return $this->belongsTo(User::class); }
 public function attachmentUrl(): ?string { return $this->attachment_path?URL::temporarySignedRoute('quote-attachments.download',now()->addDays(config('attachments.link_expiration_days',30)),['quoteMessage'=>$this]):null; }
 public function shouldAttachToEmail(): bool { return $this->attachment_path&&$this->attachment_size<=config('attachments.email_max_bytes',20*1024*1024); }
}
