<?php

namespace App\Services;

use App\Repositories\Contracts\EmployerRepository;

class EmployerService
{

    public function __construct(protected EmployerRepository $repository) {}

    
}