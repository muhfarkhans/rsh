<!DOCTYPE html>
<html>

<head>
    <title>RSH Mail</title>
</head>

<body>
    <h1>Kunjungan Baru</h1>
    <table>
        <tr>
            <td>Client Id</td>
            <td>:</td>
            <td>{{ $data['client_reg_id'] }}</td>
        </tr>
        <tr>
            <td>Nama Client</td>
            <td>:</td>
            <td>{{ $data['client_name'] }}</td>
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
</body>

</html>