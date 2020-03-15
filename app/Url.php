<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Url extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'urls';

    /**
     * Get the clicks for the url.
     */
    public function clicks()
    {
        return $this->hasMany('App\Click');
    }

    /**
     * Get the user that owns the url.
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
