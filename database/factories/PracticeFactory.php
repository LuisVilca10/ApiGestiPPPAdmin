<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Practice>
 */
class PracticeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name_empresa' => $this->faker->company,
            'ruc' => $this->faker->numerify('###########'),
            'name_represent' => $this->faker->firstName,
            'lastname_represent' => $this->faker->lastName,
            'trate_represent' => $this->faker->randomElement(['Dr', 'Lic', 'Ing', 'Mgs', 'Arq', 'Abog', 'Psic', 'Enf', 'PhD', 'Tec', 'MBA']),
            'phone_represent' => $this->faker->phoneNumber,
            'activity_student' => $this->faker->word,
            'hourse_practice' => $this->faker->numberBetween(1, 40),
            'user_id' => $this->faker->randomElement([1, 2]),  // Crea un usuario asociado a esta práctica
        ];
    }
}
