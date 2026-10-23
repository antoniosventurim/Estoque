<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use LogsActivity, SoftDeletes;

    protected $fillable = ['name', 'abbreviation'];

    public function products()
    {
        return $this->hasMany(Product::class, 'unit', 'abbreviation');
    }
}
