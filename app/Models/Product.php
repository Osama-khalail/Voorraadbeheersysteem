<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'naam',
        'type',
        'sku',
        'leverancier',
        'omschrijving',
        'minimale_voorraad',
        'foto_url',
        'categorie_id',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'categorie_id');
    }

    public function stock()
    {
        return $this->hasOne(Stock::class, 'product_id');
    }

    public function logs()
    {
        return $this->hasMany(StockLog::class, 'product_id');
    }
}
