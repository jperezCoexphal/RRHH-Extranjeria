<?php

namespace App\Http\Controllers;

use App\Services\EmployerService;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;

class EmployerController extends Controller
{

    public function __construct(protected EmployerService $service) {}

    // GET EMPLOYERS METHODS
    /*-----------------------------------------------------------------------------------*/

    public function index(){}

    public function show(){}

    // CREATE NEW EMPLOYERS METHODS
    /*-----------------------------------------------------------------------------------*/
    
    public function create(){}

    public function store(){}

    // UPDATE EMPLOYERS METHODS
    /*-----------------------------------------------------------------------------------*/
    
    public function edit(){}
    
    public function update(){}

    // DELETE EMPLOYERS METHODS
    /*-----------------------------------------------------------------------------------*/
    
    public function destroy(){}

}
