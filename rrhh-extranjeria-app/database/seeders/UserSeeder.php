<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@rrhh.es'],
            [
                'first_name' => 'Admin',
                'last_name' => 'Sistema',
                'legal_name' => 'Administrador del Sistema',
                'dni' => '00000000A',
                'phone_number' => '+34 600 000 000',
                'email' => 'admin@rrhh.es',
                'password' => Hash::make('12345678'),
                'email_verified_at' => now(),
            ]
        );
    }
}
