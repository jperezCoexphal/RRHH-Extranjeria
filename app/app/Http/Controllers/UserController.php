<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    
    public function index()
    {
        // $users = User::where('age', '<', '25')->get();
        // $users = DB::table('users')->select('*');
        $users = DB::select("SELECT * FROM users WHERE age > 18");
        // return dd($users[0]->name);
        return view('users.users_view', compact('users'));
    }

    public function create()
    {
        $user = new User;
        $user->name = 'Paco';
        $user->email = 'paco@mail.com';
        $user->password = Hash::make('1234');
        $user->age = 25;
        $user->nationality = 'España';

        $user->save();

        User::create([
            'name' => 'Manolo',
            'email' => 'manolete@manolete.com',
            'password' => Hash::make('1234'),
            'age' => 33,
            'nationality' => 'Ecuador'
        ]);

        User::create([
            'name' => 'Jose',
            'email' => 'jose@joselete.com',
            'password' => Hash::make('1234'),
            'age' => '18',
            'nationality' => 'Venezuela'
        ]);

        return redirect()->route('user.index');
    }
}
