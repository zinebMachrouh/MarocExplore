<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'location', 'list'];

    public function routes()
    {
        return $this->belongsToMany(Route::class,'destination_route');
    }

}
