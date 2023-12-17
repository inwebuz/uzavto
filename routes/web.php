<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    $res = Http::withHeader('Accept', 'application/json')->post('https://savdo.uzavtosanoat.uz/b/ap/stream/ph&models', [
        'is_web' => 'Y',
        'filial_id' => 100,
    ]);
    $data = $res->json();
    foreach ($data as $model) {
        if (!empty($model['name'])) {
            echo $model['name'] . '<br>';
        }
    }
});
