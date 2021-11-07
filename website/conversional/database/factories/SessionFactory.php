<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SessionFactory extends Factory
{
    public function definition()
    {
        $data = $this->faker->boolean(90) ? $this->faker->dateTimeBetween($startDate = '-30 days', $endDate = '-15 days') : null;

        return [
            'activated' => $this->faker->boolean(90) ? $data : null,
            'appointment' => $this->faker->boolean(90) ? $data : null,
        ];
    }
}
