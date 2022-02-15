<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Foundation\Application;
use Illuminate\Contracts\Encryption\Encrypter;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    /*
    protected $except = [
        //
        '/AAGXPC-OdmO689yu5-L0iN4mhQ-icF0xDbo/webhook',
    ];
    */
    public function __construct(Application $app, Encrypter $encrypter) {
        parent::__construct($app, $encrypter);
        $this->except = [
           '/'.env("TELEGRAM_HASH_URL").'/webhook'
        ];
    }
}
