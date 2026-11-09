<?php

namespace App\Console\Commands;

use App\Services\AlerteTunnel;
use Illuminate\Console\Command;

class VerifierAlertesTunnel extends Command
{
    protected $signature = 'lots:alertes-tunnel';

    protected $description = 'Crée une notification pour chaque lot resté plus de 24 h en tunnel';

    public function handle(): int
    {
        $this->info(AlerteTunnel::verifier() . ' notification(s) créée(s).');

        return self::SUCCESS;
    }
}
