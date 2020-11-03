<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LogFeedback extends Model
{
    //
    protected $table = 'log_feedback';
    public function Pengunjung()
    {
        return $this->belongsTo('App\DataPengunjung', 'chatid', 'chatid');
    }
}
