<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Click extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'clicks';

    /**
     * Get the url that owns the click.
     */
    public function url()
    {
        return $this->belongsTo('App\Url');
    }
}
