<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bill extends Model
{
    use HasFactory;

    protected $table = 'bills';

    protected $fillable = ['date', 'amount', 'type','image_path'];

    /**
     * Get the bill type that owns the Bill
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function billType(): BelongsTo
    {
        return $this->belongsTo(BillType::class, 'type');
    }
}
