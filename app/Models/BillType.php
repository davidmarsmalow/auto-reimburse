<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillType extends Model
{
    use HasFactory;
    public $timestamps = false;

    /**
     * Get all of the bill for the BillType
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }
}
