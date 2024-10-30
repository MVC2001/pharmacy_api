<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicineCategory extends Model
{
  use HasFactory;

    // Define table name (if it's not plural)
    protected $table = 'medicine_categories';

    // Set primary key
    protected $primaryKey = 'medicine_category_id';

    // Set fillable attributes
    protected $fillable = [
        'medicine_category',
    ];

    // Define relationships
    public function inventories()
    {
        return $this->hasMany(MedicineInventory::class, 'medicine_category_id', 'medicine_category_id');
    }
}
