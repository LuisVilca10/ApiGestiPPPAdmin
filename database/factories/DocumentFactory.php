<?php

namespace Database\Factories;

use App\Models\Practice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'document_type' => $this->faker->randomElement([
                'Carta Presentacion',
                'Carta Aceptacion',
                'Plan de Practicas',
                'Evaluacion de Practicas',
                'Informe de Practicas',
                'Monitoreo y Evaluacion de Practicas'
            ]),
            'document_path' => $this->faker->filePath(),
            'document_status' => $this->faker->randomElement(['Aprobado', 'En Proceso', 'Denegado']),
            'practice_id' => $this->faker->randomElement([1, 5]),
        ];
    }
}
