<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class SmsMessage extends Model {
 protected $fillable=['sms_campaign_id','quote_request_id','recipient_name','phone','email','message','is_test','status','email_status','whatsapp_status','provider','provider_message_id','error_message','email_error','provider_response','sent_at','email_sent_at','whatsapp_sent_at'];
 protected function casts(): array{return ['is_test'=>'boolean','provider_response'=>'array','sent_at'=>'datetime','email_sent_at'=>'datetime','whatsapp_sent_at'=>'datetime'];}
 public function whatsappUrl(): ?string {if(!$this->phone)return null;return 'https://wa.me/'.ltrim($this->phone,'+').'?text='.rawurlencode($this->message);}
 public function campaign(): BelongsTo{return $this->belongsTo(SmsCampaign::class,'sms_campaign_id');}
 public function quoteRequest(): BelongsTo{return $this->belongsTo(QuoteRequest::class);}
}
