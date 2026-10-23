<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use LogsActivity;

    protected $fillable = ['name'];

    public function movements(): HasMany
    {
        return $this->hasMany(Movement::class);
    }
}
