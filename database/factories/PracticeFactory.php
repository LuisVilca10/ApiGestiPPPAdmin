<?php

namespace Database\Factories;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PracticeFactory extends Factory
{
    public function definition(): array
    {
        $empresa = Empresa::create([
            'ruc'                => $this->faker->randomElement(['10', '20']) . $this->faker->numerify('#########'),
            'name_empresa'       => $this->faker->company,
            'name_represent'     => $this->faker->firstName,
            'lastname_represent' => $this->faker->lastName,
            'trate_represent'    => $this->faker->randomElement(['Dr', 'Lic', 'Ing', 'Mgs', 'Arq']),
            'phone_represent'    => $this->faker->numerify('9########'),
        ]);

        return [
            'empresa_id'       => $empresa->id,
            'activity_student' => $this->faker->sentence(4),
            'hourse_practice'  => $this->faker->numberBetween(240, 480),
            'status'           => 'Pendiente',
            'user_id'          => User::whereHas('roles', fn($q) => $q->where('name', 'Estudiante'))
                                      ->inRandomOrder()->value('id') ?? 2,
        ];
    }
}
