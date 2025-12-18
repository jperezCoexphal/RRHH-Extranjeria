<?php

namespace App\Filters\Foreigner;

use App\Filters\Filter;
use Closure;

class FilterByNationality implements Filter
{
    public function handle($request, Closure $next)
    {
        $builder = $next($request);

        if (!request()->has('nationality') || !request()->filled('nationality')) {
            return $builder;
        }

        return $builder->where('nationality', 'LIKE', '%' . request('nationality') . '%');
    }
}
