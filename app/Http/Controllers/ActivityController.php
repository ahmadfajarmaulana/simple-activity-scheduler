<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Activity;
use App\Helpers\WilayahHelper;
use App\Services\BmkgService;
use Carbon\Carbon;

class ActivityController extends Controller
{
    public function index()
    {
        $wilayahList = WilayahHelper::getWilayahList();
        $activities = Activity::latest()->take(10)->get();

        return view('form', compact('wilayahList', 'activities'));
    }

    public function schedule(Request $request, BmkgService $bmkgService)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'location' => 'required|string',
            'preferred_date' => 'required|date|after_or_equal:today',
        ]);

        $forecast = $bmkgService->getForecastByLocation($validated['location']);

        if ($request->has('simulate_bad_weather')) {
            $forecast = [
                'data' => [
                    [
                        'datetime' => Carbon::parse($validated['preferred_date'])->format('Y-m-d') . ' 09:00',
                        'weather' => 'Hujan Lebat',
                    ],
                    [
                        'datetime' => Carbon::parse($validated['preferred_date'])->addDay()->format('Y-m-d') . ' 09:00',
                        'weather' => 'Berkabut',
                    ],
                    [
                        'datetime' => Carbon::parse($validated['preferred_date'])->addDays(2)->format('Y-m-d') . ' 09:00',
                        'weather' => 'Hujan Sedang',
                    ],
                ]
            ];
        }

        if (!$forecast || !isset($forecast['data'])) {
            return back()->withErrors(['Gagal mengambil data dari BMKG. Coba lagi nanti.'])->withInput();
        }

        $suggestion = $bmkgService->getSuggestedTimeSlot($forecast, $validated['preferred_date']);

        if (!$request->has('simulate_bad_weather')) {
            $activity = Activity::create([
                'name' => $validated['name'],
                'location' => $validated['location'],
                'preferred_date' => $validated['preferred_date'],
                'suggested_time_slot' => $suggestion['time'],
                'weather' => $suggestion['weather'],
            ]);
        } else {
            $activity = (object) [
                'name' => $validated['name'],
                'location' => $validated['location'],
                'preferred_date' => $validated['preferred_date'],
                'suggested_time_slot' => $suggestion['time'],
                'weather' => $suggestion['weather'],
            ];
        }

        $wilayahList = WilayahHelper::getWilayahList();
        $activities = Activity::latest()->take(10)->get();

        $weatherWarning = collect($forecast['data'])->every(function ($slot) {
            $rawWeather = $slot['weather'] ?? $slot['cuaca'] ?? '';
            $weather = is_string($rawWeather) ? strtolower($rawWeather) : '';

            return str_contains($weather, 'hujan') || str_contains($weather, 'berkabut');
        });

        $forecastSlots = $bmkgService->getForecastSlots($forecast);

        return view('form', [
            'success' => true,
            'suggested_time' => $suggestion['time'],
            'weather' => $suggestion['weather'],
            'activity' => $activity,
            'wilayahList' => $wilayahList,
            'activities' => $activities,
            'weather_warning' => $weatherWarning,
            'forecast_3_days' => $forecastSlots,
        ]);
    }

    public function search(Request $request)
    {
        $keyword = strtolower($request->get('q', ''));
        $results = [];

        $wilayahList = WilayahHelper::getWilayahList();

        foreach ($wilayahList as $kode => $nama) {
            if (stripos($nama, $keyword) !== false) {
                $results[] = [
                    'kode' => $kode,
                    'nama' => $nama
                ];
            }

            if (count($results) >= 50) break;
        }

        return response()->json($results);
    }
}
