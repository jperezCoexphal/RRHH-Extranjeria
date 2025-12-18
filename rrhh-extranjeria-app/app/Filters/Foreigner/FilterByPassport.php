<?php

namespace App\Filters\Foreigner;

use App\Filters\Filter;
use Closure;

class FilterByPassport implements Filter
{
    public function handle($request, Closure $next)
    {
        $builder = $next($request);

        if (!request()->has('passport') || !request()->filled('passport')) {
            return $builder;
        }

        return $builder->where('passport', 'LIKE', '%' . request('passport') . '%');
    }
}
