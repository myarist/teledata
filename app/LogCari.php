<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LogCari extends Model
{
    //
    protected $table = 'log_cari'; 
    protected $fillable = ['username','chatid','command','keyword'];
}
