<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Event;
use App\Models\TicketType;

class ImportEvents extends Command
{
    protected $signature = 'events:import';
    protected $description = 'Import (seed) realistic event data';

    public function handle()
    {
        $this->info('Starting data seed...');

        $faker = \Faker\Factory::create('et_EE');

        for ($i = 0; $i < 20; $i++) {
            $title = $faker->catchPhrase;
            $event = Event::create([
                'title' => ucfirst($title),
                'description' => $faker->paragraph(3),
                'image_url' => 'https://picsum.photos/seed/' . $i . '/800/600',
                'location' => $faker->city,
                'start_time' => $faker->dateTimeBetween('now', '+3 months'),
            ]);

            $this->info("Created event: {$event->title}");

            $ticketCount = rand(1, 4);
            for ($j = 0; $j < $ticketCount; $j++) {
                TicketType::create([
                    'event_id' => $event->id,
                    'name' => $j === 0 ? 'Tavapilet' : ($j === 1 ? 'Sooduspilet' : 'VIP Pilet'),
                    'price' => $faker->randomFloat(2, 10, 150),
                    'currency' => 'EUR',
                ]);
            }
        }

        $this->info('Data seeded successfully!');
    }
}
