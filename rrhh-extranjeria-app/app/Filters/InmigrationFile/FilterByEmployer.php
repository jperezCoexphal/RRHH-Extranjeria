<?php

namespace App\Filters\InmigrationFile;

use App\Filters\Filter;
use Closure;

class FilterByEmployer implements Filter
{
    public function handle($request, Closure $next)
    {
        $builder = $next($request);

        if (! request()->has('employer_id') || ! request()->filled('employer_id')) {
            return $builder;
        }

        return $builder->where('employer_id', request('employer_id'));
    }
}
