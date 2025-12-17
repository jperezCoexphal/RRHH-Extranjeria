<?php

namespace App\Repositories\Eloquent;

use App\Models\Employer;
use App\Repositories\Contracts\EmployerRepository;

class EloquentEmployerRepository implements EmployerRepository
{

    public function __construct(protected Employer $employer) {}

    
}