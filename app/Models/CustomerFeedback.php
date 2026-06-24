<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class CustomerFeedback extends Model {
    protected $table='customer_feedback';
    protected $fillable=['reference','quote_request_id','customer_name','email','phone','type','category','rating','message','status','priority','admin_response','handled_at','handled_by'];
    protected function casts(): array { return ['rating'=>'integer','handled_at'=>'datetime']; }
    public function quoteRequest(): BelongsTo { return $this->belongsTo(QuoteRequest::class); }
    public function handler(): BelongsTo { return $this->belongsTo(User::class,'handled_by'); }
}
