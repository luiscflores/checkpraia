<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Minishlink\WebPush\VAPID;

class GenerateVapidKeys extends Command
{
    protected $signature = 'vapid:generate';
    protected $description = 'Generate VAPID keys for Web Push notifications';

    public function handle(): int
    {
        $keys = VAPID::createVapidKeys();

        $this->info('VAPID keys generated. Add these to your .env file:');
        $this->newLine();
        $this->line('VAPID_PUBLIC_KEY=' . $keys['publicKey']);
        $this->line('VAPID_PRIVATE_KEY=' . $keys['privateKey']);
        $this->newLine();
        $this->warn('After adding to .env, run: php artisan config:clear');

        return Command::SUCCESS;
    }
}
