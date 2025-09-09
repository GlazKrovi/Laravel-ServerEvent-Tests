<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FakeFolderMoverService;
use Illuminate\Support\Facades\Cache;

class ProgressController extends Controller
{
    public function streamProgression(Request $request)
    {
        $id = $request->query('id');
        $type = $request->query('type', 'random'); // 'random' ou 'realistic'

        $headers = [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => 'Cache-Control',
        ];

        return response()->stream(function () use ($id, $type) {
            $currentProgress = 0;
            $steps = 0;
            $maxSteps = 50;
            
            while (true) {
                // Si c'est le premier appel, initialiser la progression
                if ($steps === 0) {
                    Cache::put("progress:$id", 0);
                }
                
                // Simuler la progression directement ici
                if ($currentProgress < 100 && $steps < $maxSteps) {
                    if ($type === 'realistic') {
                        // Progression réaliste avec phases
                        if ($currentProgress < 20) {
                            $increment = rand(1, 3);
                            $delay = rand(200_000, 500_000);
                        } elseif ($currentProgress < 80) {
                            $increment = rand(3, 10);
                            $delay = rand(50_000, 200_000);
                        } else {
                            $increment = rand(1, 4);
                            $delay = rand(100_000, 400_000);
                        }
                    } else {
                        // Progression aléatoire
                        $increment = rand(1, 8);
                        $delay = rand(50_000, 300_000);
                    }
                    
                    $newProgress = min($currentProgress + $increment, 100);
                    $currentProgress = $newProgress;
                    $steps++;
                    
                    Cache::put("progress:$id", $currentProgress);
                }
                
                echo "data: " . $currentProgress . "\n\n";
                ob_flush();
                flush();
                
                if ($currentProgress >= 100) {
                    FakeFolderMoverService::removeProgress($id);
                    break;
                }
                
                // Délai variable selon le type
                if ($type === 'realistic') {
                    if ($currentProgress < 20) {
                        usleep(rand(200_000, 500_000));
                    } elseif ($currentProgress < 80) {
                        usleep(rand(50_000, 200_000));
                    } else {
                        usleep(rand(100_000, 400_000));
                    }
                } else {
                    usleep(rand(50_000, 300_000));
                }
            }
        }, 200, $headers);
    }

    public function showCache(Request $request)
    {
        $id = $request->query('id');
        $progress = Cache::get("progress:$id");

        $headers = [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => 'Content-Type',
        ];

        if ($progress === null) {
            return response()->json([
                'message' => "Aucune donnée de progression trouvée pour l'id $id."
            ], 404)->withHeaders($headers);
        }

        return response()->json([
            'id' => $id,
            'progress' => $progress,
        ], 200)->withHeaders($headers);
    }
}
