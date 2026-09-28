<?php

namespace Database\Factories;

use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Site> */
class SiteFactory extends Factory
{
    public function definition(): array
    {
        $domain = fake()->unique()->domainWord().'.dz';

        return [
            'user_id' => User::factory()->state(['role' => User::PUBLISHER]),
            'name' => ucfirst(fake()->words(2, true)),
            'url' => 'https://'.$domain,
            'domain' => $domain,
            'language' => fake()->randomElement(['fr', 'ar', 'en']),
            'category' => fake()->randomElement(config('marketplace.categories')),
            'description' => fake()->sentence(),
            'price_dzd' => fake()->numberBetween(5, 60) * 1000,
            'link_attribute' => 'sponsored',
            'turnaround_days' => 5,
            'monthly_traffic' => fake()->numberBetween(1000, 500000),
            'status' => Site::PENDING,
        ];
    }

    public function approved(): static
    {
        return $this->state(['status' => Site::APPROVED, 'verified_at' => now(), 'domain_rating' => fake()->numberBetween(5, 60)]);
    }
}
