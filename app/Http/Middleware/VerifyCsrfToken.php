<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */

    protected $except = [
        //
        '/3Nb71akPKQTM3jSK2BdLxT1VGq1FHfRquaGKJMTP/webhook',
        //'/1384993491:AAGXPC-OdmO689yu5-L0iN4mhQ-icF0xDbo/webhook',
    ];
}
