<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AuthorFactory extends Factory
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
            'book_id'=>$this->faker->randomDigit(),
            'author_name'=>$this->faker->firstName() . ' ' . $this->faker->lastName()
        ];
    }
}
