<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Vehicle extends Model {
    protected $fillable = ['name','slug','category','capacity','status','description','equipment','active','position'];
    protected function casts(): array { return ['equipment'=>'array','active'=>'boolean']; }
    public function images(): HasMany { return $this->hasMany(VehicleImage::class)->orderBy('position'); }
}