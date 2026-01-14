<?php

namespace App\Filters\InmigrationFile;

use App\Filters\Filter;
use Closure;

class FilterByApplicationType implements Filter
{
    public function handle($request, Closure $next)
    {
        $builder = $next($request);

        if (! request()->has('application_type') || ! request()->filled('application_type')) {
            return $builder;
        }

        return $builder->where('application_type', request('application_type'));
    }
}
