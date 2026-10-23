<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CostCenter extends Model
{
    use LogsActivity, SoftDeletes;

    protected $fillable = ['name', 'type', 'code', 'color', 'is_active'];

    public function movements(): HasMany
    {
        return $this->hasMany(Movement::class);
    }
}
