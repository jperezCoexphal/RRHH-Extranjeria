<?php

namespace App\Filters\Foreigner;

use App\Filters\Filter;
use Closure;

class FilterByGender implements Filter
{
    public function handle($request, Closure $next)
    {
        $builder = $next($request);

        if (!request()->has('gender') || !request()->filled('gender')) {
            return $builder;
        }

        return $builder->where('gender', request('gender'));
    }
}
