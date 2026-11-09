<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PastikanPengelola
{
    public function handle(Request $permintaan, Closure $lanjut): Response
    {
        abort_unless($permintaan->user()?->dapatMengelola(), 403, 'Anda tidak memiliki akses pengelolaan data master.');

        return $lanjut($permintaan);
    }
}
