<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
        $res = Http::withHeaders([
            'Accept' => 'application/json',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:120.0) Gecko/20100101 Firefox/120.0',
            'Cookie' => 'JSESSIONID=8E77DDB0C403C85311AFF08664EE66EF; lang=ru',
            'Origin' => 'https://savdo.uzavtosanoat.uz',
            'Referer' => 'https://savdo.uzavtosanoat.uz/',
        ])->post('https://savdo.uzavtosanoat.uz/b/ap/stream/ph&models', [
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
            } else {
                Log::info($model);
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
