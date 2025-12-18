<?php

namespace App\Filters\Foreigner;

use App\Filters\Filter;
use Closure;

class FilterByNie implements Filter
{
    public function handle($request, Closure $next)
    {
        $builder = $next($request);

        if (!request()->has('nie') || !request()->filled('nie')) {
            return $builder;
        }

        return $builder->where('nie', 'LIKE', '%' . request('nie') . '%');
    }
}
