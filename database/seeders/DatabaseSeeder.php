<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Contraseña de los tres usuarios de prueba: password
        User::factory()->admin()->create([
            'nombre' => 'Administrador',
            'email' => 'admin@marktres.test',
        ]);

        User::factory()->docente()->create([
            'nombre' => 'Docente de prueba',
            'email' => 'docente@marktres.test',
        ]);

        User::factory()->create([
            'nombre' => 'Alumno de prueba',
            'email' => 'alumno@marktres.test',
        ]);
    }
}
