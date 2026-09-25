<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

/**
 * Endpoint yang dipanggil oleh scheduler platform (.emergent/crons.yml) maupun cron
 * shared-hosting. Dilindungi bearer secret (constant-time compare).
 */
class CronController extends Controller
{
    // Cron endpoints must ack 2xx immediately; keep the work light and fast.
    private function authorized(Request $request): bool
    {
        $secret = (string) env('KANTIN_CRON_SECRET', '');
        $provided = (string) $request->bearerToken();

        return $secret !== '' && hash_equals($secret, $provided);
    }

    public function autoCancel(Request $request): JsonResponse
    {
        if (! $this->authorized($request)) {
            return response()->json(['error' => 'unauthorized'], 401);
        }

        Artisan::call('kantin:auto-cancel-unpaid');

        return response()->json(['ok' => true, 'output' => trim(Artisan::output())]);
    }

    public function settlements(Request $request): JsonResponse
    {
        if (! $this->authorized($request)) {
            return response()->json(['error' => 'unauthorized'], 401);
        }

        Artisan::call('kantin:generate-settlements');

        return response()->json(['ok' => true, 'output' => trim(Artisan::output())]);
    }
}
