<?php

namespace Database\Seeders;

use App\Models\Court;
use Illuminate\Database\Seeder;

class CourtSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courts = [
            [
                'name' => 'Center Court',
                'description' => 'Our premier court with stadium seating and professional lighting. Perfect for competitive matches.',
                'photo_url' => 'https://images.unsplash.com/photo-1554068865-24cecd4e34b8?w=800',
                'hourly_price' => 45.00,
                'status' => 'active',
                'operating_hours' => ['start' => '08:00', 'end' => '22:00'],
            ],
            [
                'name' => 'Court 1',
                'description' => 'Standard outdoor hard court with excellent surface condition. Great for casual play.',
                'photo_url' => 'https://images.unsplash.com/photo-1622163642998-1ea32b0bbc67?w=800',
                'hourly_price' => 30.00,
                'status' => 'active',
                'operating_hours' => ['start' => '08:00', 'end' => '22:00'],
            ],
            [
                'name' => 'Court 2',
                'description' => 'Clay court ideal for players who prefer slower surface. Recently resurfaced.',
                'photo_url' => 'https://images.unsplash.com/photo-1595435934249-5df7ed86e1c0?w=800',
                'hourly_price' => 35.00,
                'status' => 'active',
                'operating_hours' => ['start' => '08:00', 'end' => '20:00'],
            ],
            [
                'name' => 'Court 3',
                'description' => 'Indoor court available year-round. Climate controlled with premium playing surface.',
                'photo_url' => 'https://images.unsplash.com/photo-1606944025910-10984eaa9e5e?w=800',
                'hourly_price' => 50.00,
                'status' => 'active',
                'operating_hours' => ['start' => '06:00', 'end' => '23:00'],
            ],
            [
                'name' => 'Court 4',
                'description' => 'Grass court maintained to championship standards. Seasonal availability.',
                'photo_url' => 'https://images.unsplash.com/photo-1617883861744-1e56537d2fb0?w=800',
                'hourly_price' => 55.00,
                'status' => 'active',
                'operating_hours' => ['start' => '09:00', 'end' => '19:00'],
            ],
            [
                'name' => 'Practice Court A',
                'description' => 'Budget-friendly practice court with ball machine available. Perfect for training.',
                'photo_url' => 'https://images.unsplash.com/photo-1587280501635-68a0e82cd5ff?w=800',
                'hourly_price' => 20.00,
                'status' => 'active',
                'operating_hours' => ['start' => '08:00', 'end' => '20:00'],
            ],
            [
                'name' => 'Practice Court B',
                'description' => 'Second practice court with wall for solo training. Great for beginners.',
                'photo_url' => 'https://images.unsplash.com/photo-1622163642998-1ea32b0bbc67?w=800',
                'hourly_price' => 20.00,
                'status' => 'active',
                'operating_hours' => ['start' => '08:00', 'end' => '20:00'],
            ],
            [
                'name' => 'VIP Court',
                'description' => 'Exclusive court with private lounge, shower facilities, and complimentary refreshments.',
                'photo_url' => 'https://images.unsplash.com/photo-1554068865-24cecd4e34b8?w=800',
                'hourly_price' => 75.00,
                'status' => 'active',
                'operating_hours' => ['start' => '07:00', 'end' => '22:00'],
            ],
            [
                'name' => 'Court 5 - Under Maintenance',
                'description' => 'Currently undergoing surface renovation. Will reopen soon.',
                'photo_url' => null,
                'hourly_price' => 30.00,
                'status' => 'disabled',
                'operating_hours' => ['start' => '08:00', 'end' => '22:00'],
            ],
        ];

        foreach ($courts as $court) {
            Court::create($court);
        }
    }
}
