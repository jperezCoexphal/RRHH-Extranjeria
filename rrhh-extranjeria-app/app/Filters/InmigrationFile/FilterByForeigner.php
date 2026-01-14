<?php

namespace App\Filters\InmigrationFile;

use App\Filters\Filter;
use Closure;

class FilterByForeigner implements Filter
{
    public function handle($request, Closure $next)
    {
        $builder = $next($request);

        if (! request()->has('foreigner_id') || ! request()->filled('foreigner_id')) {
            return $builder;
        }

        return $builder->where('foreigner_id', request('foreigner_id'));
    }
}
