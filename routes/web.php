<?php

use App\Helpers\Helper;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\HomeController;
use App\Models\ClientVisit;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/app');
})->name('home');

Route::get('/pdf', function () {
    return view('pdf');
});

Route::post('/pdf/detail', [ExportController::class, 'detailPdf'])->name('pdf.detail');
Route::get('/excel/payroll', [ExportController::class, 'exportPayroll'])->name('excel.payroll');

Route::get('/detail', function () {
    $data = [
        'client_reg_id' => 'REG123456',
        'transaction_invoice_id' => 'INV987654',
        'client_name' => 'kokoko',
        'client_phone' => '081234567890',
        'client_gender' => 'Laki-laki',
        'client_year' => '1985',
        'job' => 'Dokter',
        'address' => 'Jl. Melati No. 10, Surabaya',
        'visit_complaint' => 'Sakit kepala, pusing, mual',
        'visit_medical_history' => [
            'Hipertensi',
            'Diabetes',
            'Kolesterol tinggi'
        ],
        'visit_family_medical_history' => 'Tidak ada riwayat penyakit serius dalam keluarga.',
        'visit_medication_history' => 'Obat hipertensi dan obat diabetes.',
        'visit_sleep_start' => '22:00',
        'visit_sleep_end' => '06:00',
        'visit_exercise_name' => 'Jogging',
        'visit_exercise_intensity' => 'Sedang',
        'visit_exercise_time' => 'Pagi hari, 30 menit',
        'visit_nutrition_name' => 'Makanan Sehat',
        'visit_nutrition_portion' => 'Porsi sedang',
        'visit_nutrition_time' => 'Tiga kali sehari',
        'visit_nutrition_type' => [
            'Sayur-sayuran',
            'Buah-buahan',
            'Protein',
        ],
        'visit_spiritual_name' => 'Sholat 5 waktu',
        'visit_spiritual_type' => [
            'Sholat Subuh',
            'Sholat Zuhur',
            'Sholat Ashar',
            'Sholat Maghrib',
            'Sholat Isya',
        ],
        'visit_check_temperature' => '36.7°C',
        'visit_check_blood_pressure' => '120/80',
        'visit_check_pulse' => '75',
        'visit_check_respiratory' => '16',
        'visit_check_weight' => '70',
        'visit_check_height' => '170',
        'visit_check_other' => 'Tidak ada masalah lain',
        'visit_diagnose' => 'Pusing akibat stress, kemungkinan migrain.',
        'service_name' => 'Bekam kering',
        'service_price' => '100000',
        'service_therapist' => 'Lalala',
        'client_name_related' => 'Nananna',
        'signature_therapist' => Helper::getFileAsBase64('68f5e449-75e7-47f9-bdcd-148a7328f432.png'),
        'signature_client' => Helper::getFileAsBase64('a1955a8b-3502-47f1-ac16-e4dacbc149f1.png'),
    ];

    return view('pdf.detail', ['data' => $data]);
});
