<?php
namespace Mpociot\Cockpit;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Widget
 * @package Mpociot\Cockpit
 */
class Widget extends Model
{

    /**
     * @var array
     */
    protected $casts = [
        "filters" => "array"
    ];

    /**
     * @var array
     */
    protected $hidden = [
        "created_at", "updated_at"
    ];

}