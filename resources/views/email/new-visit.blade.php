<!DOCTYPE html>
<html>

<head>
    <title>RSH Mail</title>
</head>

<body>
    <h1>Kunjungan Masuk</h1>

    <h3 style="margin-bottom: 5px">Info Client</h3>
    <table>
        <tr>
            <td>Client Id</td>
            <td>:</td>
            <td>{{ $data['client_reg_id'] }}</td>
        </tr>
        <tr>
            <td>Nama Client</td>
            <td>:</td>
            <td style="font-weight: bold">{{ $data['client_name'] }}</td>
        </tr>
        <tr>
            <td>Therapist</td>
            <td>:</td>
            <td>{{ $data['client_therapist'] }}</td>
        </tr>
        <tr>
            <td>Tanggal Kunjungan</td>
            <td>:</td>
            <td>{{ $data['client_created_at'] }}</td>
        </tr>
    </table>

    <h3 style="margin-bottom: 5px">Info Layanan</h3>
    <table>
        @if ($data['client_service'] != "")
            <tr>
                <td>Layanan</td>
                <td>:</td>
                <td style="font-weight: bold">{{ $data['client_service'] }}</td>
            </tr>
            <tr>
                <td>Tipe Layanan</td>
                <td>:</td>
                <td>{{ $data['client_service_is_cupping'] == 1 ? "Bekam" : "Non Bekam" }}</td>
            </tr>
            <tr>
                <td>Harga Layanan</td>
                <td>:</td>
                <td>{{ $data['client_service_price'] }}</td>
            </tr>
            <tr>
                <td>Komisi</td>
                <td>:</td>
                <td>{{ $data['client_service_commision'] }}</td>
            </tr>
            <tr>
                <td>Status Layanan</td>
                <td>:</td>
                <td style="font-weight: bold">{{ $data['client_service_status'] }}</td>
            </tr>
            <tr>
                <td>Layanan dimulai</td>
                <td>:</td>
                <td>{{ $data['client_service_started_at'] }}</td>
            </tr>
            <tr>
                <td>Layanan selesai</td>
                <td>:</td>
                <td>{{ $data['client_service_finished_at'] }}</td>
            </tr>
        @endif
    </table>
</body>

</html>