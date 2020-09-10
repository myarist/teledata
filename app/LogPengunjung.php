<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LogPengunjung extends Model
{
    //
    protected $table = 'log_posisi'; 
    protected $fillable = ['username','chatid','command'];
}
