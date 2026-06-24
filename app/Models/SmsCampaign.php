<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class SmsCampaign extends Model {
 protected $fillable=['name','sender','channel','message','email_subject','audience','status','scheduled_at','sent_at','recipient_count','sent_count','failed_count','email_sent_count','email_failed_count','whatsapp_sent_count','created_by'];
 protected function casts(): array{return ['scheduled_at'=>'datetime','sent_at'=>'datetime'];}
 public function messages(): HasMany{return $this->hasMany(SmsMessage::class);}
 public function creator(): BelongsTo{return $this->belongsTo(User::class,'created_by');}
}
