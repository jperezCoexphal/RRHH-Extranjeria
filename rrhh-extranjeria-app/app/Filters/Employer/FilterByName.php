<?php

namespace App\Filters\Employer;

use App\Filters\Filter;
use Closure;
use Illuminate\Database\Eloquent\Builder;

class FilterByName implements Filter
{
    public function handle($request, Closure $next)
    {
        $builder = $next($request);

        if (!request()->has('name') || !request()->filled('name')) {
            return $builder;
        }

        $name = request('name');

        return $builder->where(function (Builder $query) use ($name) {
            $query->where('fiscal_name', 'LIKE', "%{$name}%")
                  ->orWhere('comercial_name', 'LIKE', "%{$name}%");
        });
    }
}
