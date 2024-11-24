<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph,
            'start_date' => $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'due_date' => $this->faker->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
            'project_id' => \App\Models\Project::inRandomOrder()->first()->id, // Random project
            'assign_to' => \App\Models\User::inRandomOrder()->first()->id,     // Random user
            'priority_id' => \App\Models\TaskPriority::inRandomOrder()->first()->id, // Random priority
            'type_id' => \App\Models\TaskType::inRandomOrder()->first()->id,     // Random task type
            'status_id' => \App\Models\TaskStatus::inRandomOrder()->first()->id, // Random task status
            'spent_time' => $this->faker->numberBetween(0, 40), // Random spent time
            'estimated_time' => $this->faker->numberBetween(20, 60), // Random estimated time
        ];
    }
}
