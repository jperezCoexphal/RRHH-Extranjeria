<?php

namespace App\Filters\InmigrationFile;

use App\Filters\Filter;
use Closure;

class FilterByStatus implements Filter
{
    public function handle($request, Closure $next)
    {
        $builder = $next($request);

        if (! request()->has('status') || ! request()->filled('status')) {
            return $builder;
        }

        return $builder->where('status', request('status'));
    }
}
