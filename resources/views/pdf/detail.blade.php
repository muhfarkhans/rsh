<!doctype html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Invoice</title>

    <style>
        h4 {
            margin: 0;
        }

        .w-full {
            width: 100%;
        }

        .w-half {
            width: 50%;
        }

        .w-3/4 {
            width: 75%;
        }

        .w-1/4 {
            width: 25%;
        }

        .margin-top {
            margin-top: 1.25rem;
        }

        .footer {
            font-size: 0.875rem;
            padding: 1rem;
            background-color: rgb(241 245 249);
        }

        table {
            width: 100%;
            border-spacing: 0;
        }

        table.products {
            font-size: 0.875rem;
        }

        table.products tr {
            background-color: rgb(96 165 250);
        }

        table.products th {
            color: #ffffff;
            padding: 0.5rem;
        }

        table tr.items {
            background-color: rgb(241 245 249);
        }

        table tr.items td {
            padding: 0.5rem;
        }

        .total {
            text-align: right;
            margin-top: 1rem;
            font-size: 0.875rem;
        }

        .flex {
            display: flex;
        }

        .justify-content-center {
            justify-content: center;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        td {
            padding: 10px 0px;
        }

        p,
        h1,
        h2,
        h3,
        h4,
        h5 {
            margin: 0;
        }
    </style>
</head>

<body style="">
    <div style="display: flex; justify-content: center; align-items: center; ">
        <div style="width: 100%">
            <div class="flex justify-content-center w-full" style="margin-top: 5px">
                <div style="">
                    <h2 class="text-center">Rumah Sehat Holistik</h2>
                    <h3 class="text-center">Islami & Integratif</h3>
                    <h5 style="margin-top: 5px" class="text-center">Jl. Raya Wisma Pagesangan No.79, Pagesangan, Kec.
                        Jambangan, Surabaya, Jawa
                        Timur
                        60233
                    </h5>
                </div>
            </div>

            <div class="flex justify-content-center w-full" style="margin-top: 20px">
                <div style="">
                    <h2 class="text-center">Detail Pemeriksaan</h2>
                </div>
            </div>

            <!-- Section A: Identitas Diri -->
            <div class="w-full" style="margin-top: 20px;">
                <h3>A. Identitas Diri</h3>
                <table style="margin-top: 10px;">
                    <tr>
                        <td style="width: 300px; padding-bottom: 10px;">No Registrasi</td>
                        <!-- Padding for space between rows -->
                        <td>: {{ $data['client_reg_id'] }}</td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 10px;">Invoice</td>
                        <td>: {{ $data['transaction_invoice_id'] }}</td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 10px;">Nama</td>
                        <td>: {{ $data['client_name'] }}</td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 10px;">No Telepon</td>
                        <td>: {{ $data['client_phone'] }}</td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 10px;">Jenis Kelamin</td>
                        <td>: {{ $data['client_gender'] }}</td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 10px;">Tahun Lahir</td>
                        <td>: {{ $data['client_year'] }}</td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 10px;">Pekerjaan</td>
                        <td>: {{ $data['job'] }}</td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 10px;">Alamat</td>
                        <td>: {{ $data['address'] }}</td>
                    </tr>
                </table>
            </div>

            <!-- Section B: Riwayat Penyakit dan Gejala -->
            <div class="w-full" style="margin-top: 20px;">
                <h3>B. Riwayat Penyakit dan Gejala</h3>
                <table style="margin-top: 10px;">
                    <tr>
                        <td style="width: 300px; padding-bottom: 10px;">Keluhan yang dirasakan</td>
                        <td>: {{ $data['visit_complaint'] }}</td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 10px;">Riwayat Medis</td>
                        <td>
                            <ul style="list-style-type: decimal; margin-left: 20px;">
                                @foreach ($data['visit_medical_history'] as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 10px;">Riwayat Penyakit Keluarga</td>
                        <td>: {{ $data['visit_family_medical_history'] }}</td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 10px;">Riwayat Pengobatan</td>
                        <td>: {{ $data['visit_medication_history'] }}</td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 10px;">Pola Tidur</td>
                        <td>: Waktu tidur {{ $data['visit_sleep_start'] }}, Waktu bangun {{ $data['visit_sleep_end'] }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 10px;">Olahraga</td>
                        <td>
                    <tr>
                        <td style="padding-bottom: 10px;">Jenis olahraga</td>
                        <td>: {{ $data['visit_exercise_name'] }}</td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 10px;">Intensitas olahraga</td>
                        <td>: {{ $data['visit_exercise_intensity'] }}</td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 10px;">Waktu olahraga</td>
                        <td>: {{ $data['visit_exercise_time'] }}</td>
                    </tr>
                    </td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 10px;">Makanan</td>
                        <td>
                    <tr>
                        <td style="padding-bottom: 10px;">Jenis Makanan</td>
                        <td>: {{ $data['visit_nutrition_name'] }}</td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 10px;">Porsi Makanan</td>
                        <td>: {{ $data['visit_nutrition_portion'] }}</td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 10px;">Waktu Makanan</td>
                        <td>: {{ $data['visit_nutrition_time'] }}</td>
                    </tr>
                    </td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 10px;">Type</td>
                        <td>
                            <ul style="list-style-type: decimal; margin-left: 20px;">
                                @foreach ($data['visit_nutrition_type'] as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 10px;">Spiritual</td>
                        <td>
                    <tr>
                        <td style="padding-bottom: 10px;">Ibadah wajib</td>
                        <td>: {{ $data['visit_spiritual_name'] }}</td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 10px;">Type</td>
                        <td>
                            <ul style="list-style-type: decimal; margin-left: 20px;">
                                @foreach ($data['visit_spiritual_type'] as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                        </td>
                    </tr>
                    </td>
                    </tr>
                </table>
            </div>

            <!-- Section C: Pemeriksaan Fisik -->
            <div class="w-full" style="margin-top: 20px;">
                <h3>C. Pemeriksaan Fisik</h3>
                <table style="margin-top: 10px;">
                    <tr>
                        <td style="width: 300px; padding-bottom: 10px;">Suhu</td>
                        <td>: {{ $data['visit_check_temperature'] }}</td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 10px;">Tekanan Darah</td>
                        <td>: {{ $data['visit_check_blood_pressure'] }} mmHg</td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 10px;">Nadi</td>
                        <td>: {{ $data['visit_check_pulse'] }}</td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 10px;">Frekuensi Nafas</td>
                        <td>: {{ $data['visit_check_respiratory'] }}</td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 10px;">Berat Badan</td>
                        <td>: {{ $data['visit_check_weight'] }} kg</td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 10px;">Tinggi Badan</td>
                        <td>: {{ $data['visit_check_height'] }} cm</td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 10px;">Lainnya</td>
                        <td>:
                            @if(isset($data['visit_check_other'][0]))
                                {{ $data['visit_check_other'][0] }} cm
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Section D: Diagnosa -->
            <div class="w-full" style="margin-top: 20px;">
                <h3>D. Diagnosa</h3>
                <table style="margin-top: 10px;">
                    <tr>
                        <td style="width: 300px; padding-bottom: 10px;">Diagnosa</td>
                        <td>: {{ $data['visit_diagnose'] }}</td>
                    </tr>
                </table>
            </div>

            <!-- Section E: Layanan yang Diberikan -->
            <div class="w-full" style="margin-top: 20px;">
                <h3>E. Layanan Yang Diberikan</h3>
                <table style="margin-top: 10px;">
                    <tr>
                        <td style="width: 300px; padding-bottom: 10px;">Nama Layanan</td>
                        <td>: {{ $data['service_name'] }}</td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 10px;">Harga Layanan</td>
                        <td>: {{ $data['service_price'] }}</td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 10px;">Nama Terapis</td>
                        <td>: {{ $data['service_therapist'] }}</td>
                    </tr>
                </table>
            </div>

            <div style="page-break-before: always;"></div>

            <div class=" w-full" style="margin-top: 20px">
                <h3>F. Titik Bekam</h3>
                <div>
                    <div
                        style="text-align: center; display: flex; justify-content: center; flex-direction: column; align-items: center">
                        <img src="{{ $data['map_base64'] }}" width="100%" alt="">
                    </div>
                </div>
            </div>

            <div style="page-break-before: always;"></div>

            <div class=" w-full" style="margin-top: 0px">
                <p><strong>{{ $data['client_name'] }}<sup>1</sup></strong> dengan ini setuju untuk mendapatkan
                    layanan <strong>{{ $data['service_name'] }}<sup>2</sup></strong> untuk
                    <strong>{{ $data['client_name'] }}<sup>3</sup></strong>(<strong>{{ $data['client_name_related'] }}<sup>4</sup></strong>)
                    menyatakan bahwa :
                </p>
                <ul style="margin-left: 20px; margin-top: 20px">
                    <li style="list-style-type: decimal">Saya dengan sadar meminta untuk dilakukan Tindakan bekam.</li>
                    <li style="list-style-type: decimal">Saya memahami prosedur tindakan bekam yang akan dilakukan serta
                        efek sampingnya.</li>
                    <li style="list-style-type: decimal">Informasi yang saya berikan kepada terapis bekam terkait
                        keadaan
                        kesehatan klien adalah benar adanya.</li>
                    <li style="list-style-type: decimal">Saya menyetujui pelaksanaan bekam dari saudara/i
                        <strong>{{ $data['service_therapist'] }}</strong> dengan kesadaran penuh tanpa paksaan dari
                        pihak manapun.
                    </li>
                </ul>

                <div style="margin-left: 20px; margin-top: 20px">
                    <p style="margin: 0px">1. Nama wali</p>
                    <p style="margin: 0px">2. Jenis terapi bekam</p>
                    <p style="margin: 0px">3. Nama pasien</p>
                    <p style="margin: 0px">4. Hubungan dengan pasien</p>
                </div>
            </div>

            <div class=" w-full" style="margin-top: 20px">
                <table style="margin-top: 10px">
                    <tr>
                        <td class="w-half">
                            <div
                                style="text-align: center; display: flex; justify-content: center; flex-direction: column; align-items: center">
                                <img src="{{ $data['signature_therapist'] }}" width="100%" alt="">
                                <h4>{{ $data['service_therapist'] }}</h4>
                                <p>Terapis</p>
                            </div>
                        </td>
                        <td class="w-half">
                            <div
                                style="text-align: center; display: flex; justify-content: center; flex-direction: column; align-items: center">
                                <img src="{{ $data['signature_client'] }}" width="100%" alt="">
                                <h4>{{ $data['client_name'] }}</h4>
                                <p>Client</p>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</body>

</html>