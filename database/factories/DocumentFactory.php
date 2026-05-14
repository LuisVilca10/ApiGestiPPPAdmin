<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\Practice;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentFactory extends Factory
{
    public function definition(): array
    {
        $type = $this->faker->randomElement(Document::TIPOS_PERMITIDOS);

        return [
            'document_type'   => $type,
            'document_name'   => $type . ' - ' . $this->faker->name(),
            'document_path'   => 'practicas/fake_' . $this->faker->uuid() . '.pdf',
            'document_status' => $this->faker->randomElement(['En Proceso', 'Aprobado', 'Rechazado']),
            'practice_id'     => Practice::inRandomOrder()->value('id') ?? 1,
        ];
    }
}
