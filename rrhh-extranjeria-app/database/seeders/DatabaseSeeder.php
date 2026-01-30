<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seeders en orden de dependencia
        $this->call([
            UserSeeder::class,         // Usuario administrador
            CountrySeeder::class,      // Primero países
            ProvinceSeeder::class,     // Luego provincias (depende de países)
            MunicipalitySeeder::class, // Finalmente municipios (depende de provincias)
            EmployerSeeder::class,
            ForeignerSeeder::class,
            InmigrationFileSeeder::class
            // UserSeeder::class          // Seeder de Usuarios
        ]);

        $this->command->info('Database seeded successfully!');
    }
}
