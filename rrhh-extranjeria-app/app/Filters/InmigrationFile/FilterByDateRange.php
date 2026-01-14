<?php

namespace App\Filters\InmigrationFile;

use App\Filters\Filter;
use Closure;

class FilterByDateRange implements Filter
{
    public function handle($request, Closure $next)
    {
        $builder = $next($request);

        // Filtrar por fecha de inicio
        if (request()->has('start_date_from') && request()->filled('start_date_from')) {
            $builder->where('start_date', '>=', request('start_date_from'));
        }

        if (request()->has('start_date_to') && request()->filled('start_date_to')) {
            $builder->where('start_date', '<=', request('start_date_to'));
        }

        return $builder;
    }
}
