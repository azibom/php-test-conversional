<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    public function definition()
    {
        return [
            'email' => $this->faker->unique()->safeEmail(),
            'created' => $this->faker->dateTimeBetween($startDate = '-60 days', $endDate = '-30 days'),
        ];
    }
}
