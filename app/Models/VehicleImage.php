<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class VehicleImage extends Model {
    protected $fillable = ['vehicle_id','path','alt_text','position'];
    public function vehicle(): BelongsTo { return $this->belongsTo(Vehicle::class); }
}