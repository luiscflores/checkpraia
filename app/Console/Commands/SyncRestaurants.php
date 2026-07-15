<?php

namespace App\Console\Commands;

use App\Models\Beach;
use App\Models\Restaurant;
use App\Services\GeoService;
use App\Services\TheFork\TheForkClient;
use App\Services\Tripadvisor\TripadvisorClient;
use Illuminate\Console\Command;

class SyncRestaurants extends Command
{
    protected $signature = 'restaurants:sync {--batch=10}';

    protected $description = 'Fetch nearby restaurants from TripAdvisor, TheFork, or Overpass for all beaches';

    public function handle(TripadvisorClient $tripadvisor, TheForkClient $thefork): int
    {
        $total = Beach::whereNotNull('latitude')->whereNotNull('longitude')->count();
        $batch = (int) $this->option('batch');
        $synced = 0;

        $this->info("Syncing restaurants for {$total} beaches...");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        Beach::whereNotNull('latitude')->whereNotNull('longitude')
            ->cursor()
            ->each(function (Beach $beach) use ($tripadvisor, $thefork, &$synced, $bar) {
                $this->syncForBeach($beach, $tripadvisor, $thefork);
                $synced++;
                $bar->advance();
            });

        $bar->finish();
        $this->newLine();
        $this->info("Done! Synced restaurants for {$synced} beaches.");

        return self::SUCCESS;
    }

    private function syncForBeach(Beach $beach, TripadvisorClient $tripadvisor, TheForkClient $thefork): void
    {
        $lat = (float) $beach->latitude;
        $lon = (float) $beach->longitude;
        $sources = [
            'tripadvisor' => $tripadvisor,
            'thefork' => $thefork,
        ];

        foreach ($sources as $source => $client) {
            try {
                $restaurants = $client->getNearby($lat, $lon);

                foreach ($restaurants as $data) {
                    $distance = $data['distance'] ?? app(GeoService::class)->haversine($lat, $lon, $data['latitude'], $data['longitude']);

                    $restaurant = Restaurant::updateOrCreate(
                        ['external_id' => $data['external_id']],
                        [
                            'source' => $source,
                            'name' => $data['name'],
                            'cuisine_type' => $data['cuisine_type'] ?? null,
                            'rating' => $data['rating'] ?? null,
                            'reviews_count' => $data['reviews_count'] ?? 0,
                            'address' => $data['address'] ?? null,
                            'average_price' => $data['average_price'] ?? null,
                            'booking_url' => $data['booking_url'] ?? null,
                            'external_url' => $data['external_url'] ?? null,
                            'latitude' => $data['latitude'] ?? $lat,
                            'longitude' => $data['longitude'] ?? $lon,
                        ]
                    );

                    $beach->restaurants()->syncWithoutDetaching([
                        $restaurant->id => ['distance' => $distance],
                    ]);
                }
            } catch (\Exception $e) {
                logger()->error("Failed to sync {$source} restaurants for beach {$beach->id}: ".$e->getMessage());
            }
        }
    }
}
