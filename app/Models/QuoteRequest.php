<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class QuoteRequest extends Model {
    protected $fillable = ['reference','name','organization','phone','sms_consent','sms_consent_at','email_consent','email_consent_at','whatsapp_consent','whatsapp_consent_at','email','departure_date','return_date','origin','destination','passengers','service_type','vehicle_id','driver_required','message','status','internal_notes'];
    protected function casts(): array { return ['departure_date'=>'date','return_date'=>'date','driver_required'=>'boolean','sms_consent'=>'boolean','sms_consent_at'=>'datetime','email_consent'=>'boolean','email_consent_at'=>'datetime','whatsapp_consent'=>'boolean','whatsapp_consent_at'=>'datetime']; }
    public function vehicle(): BelongsTo { return $this->belongsTo(Vehicle::class); }
    public function messages(): \Illuminate\Database\Eloquent\Relations\HasMany { return $this->hasMany(QuoteMessage::class)->latest(); }
    public function feedback(): \Illuminate\Database\Eloquent\Relations\HasMany { return $this->hasMany(CustomerFeedback::class); }
}
