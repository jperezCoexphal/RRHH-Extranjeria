<?php

namespace App\Filters\Employer;

use App\Filters\Filter;
use Closure;

class FilterByNif implements Filter
{
    public function handle($request, Closure $next)
    {
        $builder = $next($request);

        if (!request()->has('nif') || !request()->filled('nif')) {
            return $builder;
        }

        return $builder->where('nif', 'LIKE', '%' . request('nif') . '%');
    }
}
