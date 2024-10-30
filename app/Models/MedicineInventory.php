<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicineInventory extends Model
{
    use HasFactory;

    // Define table name
    protected $table = 'medicine_inventory';

    // Set primary key
    protected $primaryKey = 'inventory_id';

    // Set fillable attributes
    protected $fillable = [
        'inventory_number',
        'medicine_category_id',
        'total_medicine',
    ];

    // Define relationships
    public function category()
    {
        return $this->belongsTo(MedicineCategory::class, 'medicine_category_id', 'medicine_category_id');
    }

    public function stocks()
    {
        return $this->hasMany(MedicineStock::class, 'inventory_id', 'inventory_id');
    }
}
