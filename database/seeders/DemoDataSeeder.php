<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventBooking;
use App\Models\EventCategory;
use App\Models\Livestream;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    /**
     * Create demo events, livestreams, and optional bookings for testing the admin panel.
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        $userId = $admin?->id ?? 1;

        $categories = [
            EventCategory::firstOrCreate(['name' => 'Basketball'], ['name' => 'Basketball']),
            EventCategory::firstOrCreate(['name' => 'Sports'], ['name' => 'Sports']),
        ];

        if (Event::count() === 0) {
            Event::create([
                'title' => 'Championship Finals',
                'description' => 'Live basketball championship finals.',
                'date' => now()->addDays(5)->toDateString(),
                'time' => '19:00',
                'total_seats' => 500,
                'available_seats' => 500,
                'coins' => 50,
                'category_id' => $categories[0]->id,
                'is_online' => false,
                'status' => 'active',
            ]);
            Event::create([
                'title' => 'Weekend Showdown',
                'description' => 'Weekend basketball showdown event.',
                'date' => now()->addDays(12)->toDateString(),
                'time' => '18:30',
                'total_seats' => 200,
                'available_seats' => 200,
                'coins' => 30,
                'category_id' => $categories[0]->id,
                'is_online' => true,
                'status' => 'active',
            ]);
        }

        if (Livestream::count() === 0) {
            Livestream::create([
                'title' => 'Live: Pro League Game 1',
                'description' => 'Watch the pro league live.',
                'scheduled_at' => now()->addDays(2),
                'agora_channel' => 'pro_league_1',
                'price' => 20,
                'max_participants' => 1000,
                'created_by' => $userId,
                'status' => 'scheduled',
            ]);
            Livestream::create([
                'title' => 'Live: Weekend Match',
                'description' => 'Weekend live match.',
                'scheduled_at' => now()->addDays(7),
                'agora_channel' => 'weekend_match',
                'price' => 15,
                'max_participants' => 500,
                'created_by' => $userId,
                'status' => 'scheduled',
            ]);
        }

        $this->command->info('Demo events and livestreams created.');
    }
}
