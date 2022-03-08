<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            //
            'name'=>$this->faker->name(),
            'isbn'=>$this->faker->isbn10(),
            'country'=>$this->faker->country(),
            'number_of_pages'=>$this->faker->randomDigit(),
            'publisher'=>$this->faker->company(),
            'release_date'=>$this->faker->date()
        ];
    }
}
