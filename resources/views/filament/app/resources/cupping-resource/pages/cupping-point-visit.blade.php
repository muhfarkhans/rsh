<x-filament-panels::page>
    <x-filament::section>
        @php
            $createdAt = $visitCupping->clientVisit->created_at;
            // $diff = $createdAt->diffForHumans();
            $date = $createdAt->format('Y-m-d');
        @endphp

        <p>{{ $date }}</p>

        <div style="display: flex; flex-direction: column; margin-top: 10px;">
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

            <!-- Consent Section -->
            <div class="w-full" style="margin-top: 50px;">
                <p>
                    <strong>{{ $data['client_name'] }}<sup>1</sup></strong> dengan ini setuju untuk mendapatkan layanan
                    <strong>{{ $data['service_name'] }}<sup>2</sup></strong> untuk
                    <strong>{{ $data['client_name'] }}<sup>3</sup></strong>
                    (<strong>{{ $data['client_name_related'] }}<sup>4</sup></strong>)
                    menyatakan bahwa:
                </p>
                <ul style="margin-left: 20px; margin-top: 20px;">
                    <li>Saya dengan sadar meminta untuk dilakukan Tindakan bekam.</li>
                    <li>Saya memahami prosedur tindakan bekam yang akan dilakukan serta efek sampingnya.</li>
                    <li>Informasi yang saya berikan kepada terapis bekam terkait keadaan kesehatan klien adalah benar
                        adanya.</li>
                    <li>Saya menyetujui pelaksanaan bekam dari saudara/i
                        <strong>{{ $data['service_therapist'] }}</strong> dengan kesadaran penuh tanpa paksaan dari
                        pihak manapun.
                    </li>
                </ul>
                <div style="margin-left: 20px; margin-top: 20px;">
                    <p style="margin: 0px;">1. Nama wali</p>
                    <p style="margin: 0px;">2. Jenis terapi bekam</p>
                    <p style="margin: 0px;">3. Nama pasien</p>
                    <p style="margin: 0px;">4. Hubungan dengan pasien</p>
                </div>
            </div>

            <!-- Signatures Section -->
            <div class="w-full" style="margin-top: 20px;">
                <table style="margin-top: 10px;">
                    <tr>
                        <td class="w-half">
                            <div
                                style="text-align: center; display: flex; justify-content: center; flex-direction: column; align-items: center;">
                                <img src="{{ $data['signature_therapist'] }}" width="100%" alt="Signature Terapis">
                                <h4>{{ $data['service_therapist'] }}</h4>
                                <p>Terapis</p>
                            </div>
                        </td>
                        <td class="w-half">
                            <div
                                style="text-align: center; display: flex; justify-content: center; flex-direction: column; align-items: center;">
                                <img src="{{ $data['signature_client'] }}" width="100%" alt="Signature Client">
                                <h4>{{ $data['client_name'] }}</h4>
                                <p>Client</p>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

    </x-filament::section>

    @livewire('map-point-skeleton', [
        'imageUrl' => '/assets/images/skeleton.jpg',
        'points' => $visitCupping->points,
        'id' => $data['id'],
        'filePdfname' => 'Detail-' . $data['client_name'] . '-' . $date,
    ])
</x-filament-panels::page>