<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class UzAutoMotorsCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uzauto:check {full?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $full = $this->argument('full');
        $res = Http::withHeader('Accept', 'application/json')->post('https://savdo.uzavtosanoat.uz/b/ap/stream/ph&models', [
            'is_web' => 'Y',
            'filial_id' => 100,
        ]);
        $data = $res->json();
        $text = '';
        $additionalNotifications = [];
        $trackingModels = [
            'gentra',
            'labo',
            'damas',
            'kobalt',
            'cobalt',
            // 'onix',
        ];
        foreach ($data as $model) {
            if (!empty($model['name'])) {
                $text .= $model['name'] . "\n";
                foreach ($trackingModels as $trackingModel) {
                    if (stripos($model['name'], $trackingModel) !== false) {
                        $additionalNotifications[] = $model['name'];
                    }
                }
            }
        }

        $botApiToken = env('TELEGRAM_BOT_TOKEN');

        if (count($additionalNotifications)) {
            foreach ($additionalNotifications as $item) {
                Http::get("https://api.telegram.org/bot{$botApiToken}/sendMessage", [
                    'chat_id' => env('TELEGRAM_CHAT_ID'),
                    'text' => $item . ' - доступен https://savdo.uzavtosanoat.uz/',
                ]);
            }
        }
        if ($full) {
            Http::get("https://api.telegram.org/bot{$botApiToken}/sendMessage", [
                'chat_id' => env('TELEGRAM_ADMIN_CHAT_ID'),
                'text' => $text,
            ]);
        }
    }
}
