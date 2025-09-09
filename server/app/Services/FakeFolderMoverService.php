<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class FakeFolderMoverService
{
    public static function simulateFakeProgress(string $id)
    {
        // Vérifier si une simulation est déjà en cours
        if (Cache::has("progress_running:$id")) {
            return;
        }
        
        // Marquer qu'une simulation est en cours
        Cache::put("progress_running:$id", true, 30); // 30 secondes max
        
        $currentProgress = 0;
        $targetProgress = 100;
        $steps = 0;
        $maxSteps = 50; // Nombre maximum d'étapes pour atteindre 100%
        
        // Simulation avec progression aléatoire
        while ($currentProgress < $targetProgress && $steps < $maxSteps) {
            // Calculer l'incrément aléatoire (entre 1 et 8)
            $increment = rand(1, 8);
            
            // S'assurer qu'on ne dépasse pas 100%
            $newProgress = min($currentProgress + $increment, $targetProgress);
            
            // Mettre à jour le cache
            Cache::put("progress:$id", $newProgress);
            $currentProgress = $newProgress;
            $steps++;
            
            // Délai aléatoire entre 50ms et 300ms
            $delay = rand(50_000, 300_000);
            usleep($delay);
        }
        
        // S'assurer qu'on termine à 100%
        Cache::put("progress:$id", 100);
        
        // Nettoyer le flag de simulation
        Cache::forget("progress_running:$id");
    }

    public static function getProgress(string $id)
    {
        return Cache::get("progress:$id", 0);
    }

    public static function removeProgress(string $id)
    {
        Cache::forget("progress:$id");
    }

    /**
     * Simulation de progression plus réaliste avec des phases
     * - Démarrage lent (0-20%)
     * - Progression rapide (20-80%) 
     * - Ralentissement final (80-100%)
     */
    public static function simulateRealisticProgress(string $id)
    {
        // Vérifier si une simulation est déjà en cours
        if (Cache::has("progress_running:$id")) {
            return;
        }
        
        // Marquer qu'une simulation est en cours
        Cache::put("progress_running:$id", true, 60); // 60 secondes max
        
        $currentProgress = 0;
        
        // Phase 1: Démarrage lent (0-20%)
        while ($currentProgress < 20) {
            $increment = rand(1, 3); // Petits incréments
            $newProgress = min($currentProgress + $increment, 20);
            Cache::put("progress:$id", $newProgress);
            $currentProgress = $newProgress;
            usleep(rand(200_000, 500_000)); // Délais plus longs
        }
        
        // Phase 2: Progression rapide (20-80%)
        while ($currentProgress < 80) {
            $increment = rand(3, 10); // Incréments plus importants
            $newProgress = min($currentProgress + $increment, 80);
            Cache::put("progress:$id", $newProgress);
            $currentProgress = $newProgress;
            usleep(rand(50_000, 200_000)); // Délais plus courts
        }
        
        // Phase 3: Ralentissement final (80-100%)
        while ($currentProgress < 100) {
            $increment = rand(1, 4); // Incréments plus petits
            $newProgress = min($currentProgress + $increment, 100);
            Cache::put("progress:$id", $newProgress);
            $currentProgress = $newProgress;
            usleep(rand(100_000, 400_000)); // Délais moyens
        }
        
        // S'assurer qu'on termine à 100%
        Cache::put("progress:$id", 100);
        
        // Nettoyer le flag de simulation
        Cache::forget("progress_running:$id");
    }
}
