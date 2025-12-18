<?php

namespace App\Filters;

use Closure;

interface Filter
{
    public function handle($request, Closure $next);
}
