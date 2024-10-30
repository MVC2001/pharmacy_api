<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicineStock extends Model
{
     use HasFactory;

    // Define table name
    protected $table = 'medicine_stock';

    // Set primary key
    protected $primaryKey = 'stock_id';

    // Set fillable attributes
    protected $fillable = [
        'inventory_id',
        'medicine_category_id',
        'quantity_in_stock',
        'last_restocked',
        'expiration_date',
    ];

    // Define relationships
    public function inventory()
    {
        return $this->belongsTo(MedicineInventory::class, 'inventory_id', 'inventory_id');
    }

    public function category()
    {
        return $this->belongsTo(MedicineCategory::class, 'medicine_category_id', 'medicine_category_id');
    }
}
