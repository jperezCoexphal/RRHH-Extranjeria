<?php

namespace App\Filters\InmigrationFile;

use App\Filters\Filter;
use Closure;

class FilterByCampaign implements Filter
{
    public function handle($request, Closure $next)
    {
        $builder = $next($request);

        if (! request()->has('campaign') || ! request()->filled('campaign')) {
            return $builder;
        }

        return $builder->where('campaign', request('campaign'));
    }
}
