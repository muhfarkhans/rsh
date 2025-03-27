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

            <div class=" w-full" style="margin-top: 20px">
                <h3>A. Identitas Diri</h3>
                <table style="margin-top: 10px">
                    <tr>
                        <td class="w-1/4">No Registrasi</td>
                        <td class="w-3/4">: {{ $data['client_reg_id'] }}</td>
                    </tr>
                    <tr>
                        <td class="w-1/4">Invoice</td>
                        <td class="w-3/4">: {{ $data['transaction_invoice_id'] }}</td>
                    </tr>
                    <tr>
                        <td class="w-1/4">Nama </td>
                        <td class="w-3/4">: {{ $data['client_name'] }}</td>
                    </tr>
                    <tr>
                        <td class="w-1/4">No Telepon</td>
                        <td class="w-3/4">: {{ $data['client_phone'] }}</td>
                    </tr>
                    <tr>
                        <td class="w-1/4">Jenis Kelamin</td>
                        <td class="w-3/4">: {{ $data['client_gender'] }}</td>
                    </tr>
                    <tr>
                        <td class="w-1/4">Tahun Lahir</td>
                        <td class="w-3/4">: {{ $data['client_year'] }}</td>
                    </tr>
                    <tr>
                        <td class="w-1/4">Pekerjaan</td>
                        <td class="w-3/4">: {{ $data['job'] }}</td>
                    </tr>
                    <tr>
                        <td class="w-1/4">Alamat</td>
                        <td class="w-3/4">: {{ $data['address'] }}</td>
                    </tr>
                </table>
            </div>

            <div class=" w-full" style="margin-top: 20px">
                <h3>B. Riwayat Penyakit dan Gejala</h3>
                <table style="margin-top: 10px">
                    <tr>
                        <td class="w-1/4">Keluhan yang dirasakan</td>
                        <td class="w-3/4">: {{ $data['visit_complaint'] }}</td>
                    </tr>
                    <tr>
                        <td class="w-1/4">Riwayat Medis</td>
                        <td class="w-3/4">
                            <ul>
                                @foreach ($data['visit_medical_history'] as $item)
                                    <li style="list-style-type: decimal">{{ $item }}</li>
                                @endforeach
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <td class="w-1/4">Riwayat penyakit keluarga</td>
                        <td class="w-3/4">: {{ $data['visit_family_medical_history'] }}</td>
                    </tr>
                    <tr>
                        <td class="w-1/4">Riwayat pengobatan</td>
                        <td class="w-3/4">: {{ $data['visit_medication_history'] }}</td>
                    </tr>
                    <tr>
                        <td class="w-1/4">Pola Tidur</td>
                        <td class="w-3/4">
                            : Waktu tidur {{ $data['visit_sleep_start'] }}, Waktu bangun
                            {{ $data['visit_sleep_end'] }}
                        </td>
                    </tr>
                    <tr>
                        <td class="w-1/4">Olahraga</td>
                        <td class="w-3/4">
                            <p>: Jenis olahraga {{ $data['visit_exercise_name'] }}</p>
                            <p>: Intensitas olahraga {{ $data['visit_exercise_intensity'] }}</p>
                            <p>: Waktu olahraga {{ $data['visit_exercise_time'] }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td class="w-1/4">Makanan</td>
                        <td class="w-3/4">
                            <p>: Jenis makanan {{ $data['visit_nutrition_name'] }}</p>
                            <p>: Porsi makanan {{ $data['visit_nutrition_portion'] }}</p>
                            <p>: Waktu makan {{ $data['visit_nutrition_time'] }}</p>
                            <ul>
                                @foreach ($data['visit_nutrition_type'] as $item)
                                    <li style="list-style-type: decimal">{{ $item }}</li>
                                @endforeach
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <td class="w-1/4">Spiritual</td>
                        <td class="w-3/4">
                            <p>: Ibadah wajib {{ $data['visit_spiritual_name'] }}</p>
                            <ul>
                                @foreach ($data['visit_spiritual_type'] as $item)
                                    <li style="list-style-type: decimal">{{ $item }}</li>
                                @endforeach
                            </ul>
                        </td>
                    </tr>
                </table>
            </div>

            <div class=" w-full" style="margin-top: 20px">
                <h3>C. Pemeriksaan Fisik</h3>
                <table style="margin-top: 10px">
                    <tr>
                        <td class="w-1/4">Suhu</td>
                        <td class="w-3/4">: {{ $data['visit_check_temperature'] }}</td>
                    </tr>
                    <tr>
                        <td class="w-1/4">Tekanan darah</td>
                        <td class="w-3/4">: {{ $data['visit_check_blood_pressure'] }} mmHg</td>
                    </tr>
                    <tr>
                        <td class="w-1/4">Nadi</td>
                        <td class="w-3/4">: {{ $data['visit_check_pulse'] }}</td>
                    </tr>
                    <tr>
                        <td class="w-1/4">Frekuensi Nafas</td>
                        <td class="w-3/4">: {{ $data['visit_check_respiratory'] }}</td>
                    </tr>
                    <tr>
                        <td class="w-1/4">Berat Badan</td>
                        <td class="w-3/4">: {{ $data['visit_check_weight'] }} kg</td>
                    </tr>
                    <tr>
                        <td class="w-1/4">Tinggi badan</td>
                        <td class="w-3/4">: {{ $data['visit_check_height'] }} cm</td>
                    </tr>
                    <tr>
                        <td class="w-1/4">Lainnya</td>
                        <td class="w-3/4">:
                            @if(isset($data['visit_check_other'][0]))
                                {{ $data['visit_check_other'][0] }} cm
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                </table>
            </div>

            <div class=" w-full" style="margin-top: 20px">
                <h3>D. Diagnosa</h3>
                <table style="margin-top: 10px">
                    <tr>
                        <td class="w-1/4">Diagnosa</td>
                        <td class="w-3/4">: {{ $data['visit_diagnose'] }}</td>
                    </tr>
                </table>
            </div>

            <div class=" w-full" style="margin-top: 20px">
                <h3>E. Layanan Yang Diberikan</h3>
                <table style="margin-top: 10px">
                    <tr>
                        <td class="w-1/4">Nama Layanan</td>
                        <td class="w-3/4">: {{ $data['service_name'] }}</td>
                    </tr>
                    <tr>
                        <td class="w-1/4">Harga Layanan</td>
                        <td class="w-3/4">: {{ $data['service_price'] }}</td>
                    </tr>
                    <tr>
                        <td class="w-1/4">Nama Terapis</td>
                        <td class="w-3/4">: {{ $data['service_therapist'] }}</td>
                    </tr>
                </table>
            </div>

            <div class=" w-full" style="margin-top: 100px">
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