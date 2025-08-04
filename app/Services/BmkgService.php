<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class BmkgService
{
    protected string $baseUrl = 'https://api.bmkg.go.id/publik/prakiraan-cuaca';

    public function getForecastByLocation(string $adm4): ?array
    {
        $url = "{$this->baseUrl}?adm4={$adm4}";

        try {
            $response = Http::timeout(10)->get($url);

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }

    public function getForecastSlots(array $forecast): array
    {
        $slots = [];

        if (!isset($forecast['data'])) {
            return $slots;
        }

        foreach ($forecast['data'] as $locationForecast) {
            if (!isset($locationForecast['cuaca'])) {
                continue;
            }

            foreach ($locationForecast['cuaca'] as $slotGroup) {
                foreach ($slotGroup as $slot) {
                    if (!isset($slot['local_datetime'], $slot['weather_desc'])) {
                        continue;
                    }

                    $slotTime = \Carbon\Carbon::parse($slot['local_datetime']);

                    if ($slotTime->lt(now()) || $slotTime->gt(now()->addDays(3))) {
                        continue;
                    }

                    $slots[] = [
                        'datetime' => $slotTime->format('Y-m-d H:i') . ' WIB',
                        'weather' => $slot['weather_desc'],
                    ];

                    if (count($slots) >= 6) break 3;
                }
            }
        }

        return $slots;
    }


    public function getSuggestedTimeSlot(array $forecast, string $preferredDate): array
    {
        if (!isset($forecast['data']) || !is_array($forecast['data'])) {
            return $this->fallbackSuggestion();
        }

        $preferred = [];
        $candidates = [];

        $now = now();
        $end = now()->addDays(3);

        foreach ($forecast['data'] as $locationForecast) {
            if (!isset($locationForecast['cuaca']) || !is_array($locationForecast['cuaca'])) {
                continue;
            }

            foreach ($locationForecast['cuaca'] as $slotGroup) {
                foreach ($slotGroup as $slot) {
                    if (!isset($slot['local_datetime'], $slot['weather_desc'])) {
                        continue;
                    }

                    $slotTime = \Carbon\Carbon::parse($slot['local_datetime']);

                    if ($slotTime->lt($now) || $slotTime->gt($end)) {
                        continue;
                    }

                    $weather = strtolower($slot['weather_desc']);

                    if (str_contains($weather, 'cerah') || str_contains($weather, 'berawan')) {
                        $slotInfo = [
                            'time' => $slotTime->format('Y-m-d H:i') . ' WIB',
                            'weather' => $slot['weather_desc'],
                            'date' => $slotTime->toDateString(),
                        ];

                        if ($slotInfo['date'] === $preferredDate) {
                            $preferred[] = $slotInfo;
                        } else {
                            $candidates[] = $slotInfo;
                        }
                    }
                }
            }
        }

        if (count($preferred)) {
            return [
                'time' => $preferred[0]['time'],
                'weather' => $preferred[0]['weather'],
            ];
        }

        if (count($candidates)) {
            return [
                'time' => $candidates[0]['time'],
                'weather' => $candidates[0]['weather'],
            ];
        }

        return $this->fallbackSuggestion();
    }

    protected function fallbackSuggestion(): array
    {
        return [
            'time' => '-',
            'weather' => 'Tidak tersedia atau cuaca buruk',
        ];
    }
}
