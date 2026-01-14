<?php

namespace App\Filters\InmigrationFile;

use App\Filters\Filter;
use Closure;

class FilterByFileCode implements Filter
{
    public function handle($request, Closure $next)
    {
        $builder = $next($request);

        if (! request()->has('file_code') || ! request()->filled('file_code')) {
            return $builder;
        }

        return $builder->where('file_code', 'LIKE', '%' . request('file_code') . '%');
    }
}
