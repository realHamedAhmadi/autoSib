<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PopulationStat>
 */
class PopulationStatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'stat'=>'1401',
            'ageType'=>'yearly',
            'center_code'=>111111,
            'center_name'=>'name',
            'unit_code'=>$this->faker->numberBetween(),
            'unit_name'=>$this->faker->name,
            'age_category'=>$this->faker->name,
            'from_birthdate'=>$this->faker->date,
            'to_birthdate'=>$this->faker->date,
            'type'=>[
                'men','women','married_women'
            ][rand(0,2)],
            'number'=>$this->faker->numberBetween(1,10)
        ];
    }
}
