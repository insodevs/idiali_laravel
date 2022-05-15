<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $uuid
 * @property string $entity_uuid
 * @property string $name
 * @property string $value
 */
class Characteristic extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['uuid', 'entity_uuid', 'name', 'value'];

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'uuid';

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
