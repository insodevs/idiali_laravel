<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $product_uuid
 * @property string $category_uuid
 */
class ProductToCategory extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['product_uuid', 'category_uuid'];

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'product_uuid';

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

}
