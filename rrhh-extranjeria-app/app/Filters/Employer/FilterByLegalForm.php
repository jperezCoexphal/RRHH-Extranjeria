<?php

namespace App\Filters\Employer;

use App\Filters\Filter;
use Closure;

class FilterByLegalForm implements Filter
{
    public function handle($request, Closure $next)
    {
        $builder = $next($request);

        if (!request()->has('legal_form') || !request()->filled('legal_form')) {
            return $builder;
        }

        return $builder->where('legal_form', request('legal_form'));
    }
}
