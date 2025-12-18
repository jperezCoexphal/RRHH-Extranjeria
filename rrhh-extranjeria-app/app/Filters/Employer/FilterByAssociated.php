<?php

namespace App\Filters\Employer;

use App\Filters\Filter;
use Closure;

class FilterByAssociated implements Filter
{
    public function handle($request, Closure $next)
    {
        $builder = $next($request);

        if (!request()->has('is_associated') || !request()->filled('is_associated')) {
            return $builder;
        }

        return $builder->where('is_associated', request()->boolean('is_associated'));
    }
}
