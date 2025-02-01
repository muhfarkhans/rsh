<?php

use App\Http\Controllers\HomeController;
use App\Models\ClientVisit;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/app');
})->name('home');

Route::get('/pdf/{id}', function ($id) {
    $visit = ClientVisit::where('id', $id)->first();

    return view('pdf', ['visit' => $visit]);
});
