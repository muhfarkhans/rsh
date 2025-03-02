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
        <div style="width: 400px">
            <div class="flex justify-content-center w-full" style="margin-top: 5px">
                <div style="width: 400px">
                    <h2 class="text-center">Rumah Sehat Holistik</h2>
                    <h3 class="text-center">Islami & Integratif</h3>
                    <h5 class="text-center">Jl. Raya Wisma Pagesangan No.79, Pagesangan, Kec. Jambangan, Surabaya, Jawa
                        Timur
                        60233
                    </h5>
                </div>
            </div>

            <div class="flex justify-content-center w-full" style="margin-top: 20px">
                <table style="width: 400px">
                    <tr>
                        <td class="w-half">Invoice Id</td>
                        <td class="w-half text-right">{{ $data['invoice_id'] }}</td>
                    </tr>
                    <tr>
                        <td class="w-half">Kasir</td>
                        <td class="w-half text-right">{{ $data['cashier_name'] }}</td>
                    </tr>
                    <tr>
                        <td class="w-half">Tanggal</td>
                        <td class="w-half text-right">{{ $data['created_at'] }}</td>
                    </tr>
                </table>
            </div>

            <div class="flex justify-content-center w-full" style="margin-top: 0px">
                <p>============================================</p>
            </div>

            <div class="flex justify-content-center w-full" style="margin-top: 0px">
                <table style="width: 400px">
                    <tr>
                        <td class="w-half">{{ $data['service_name'] }}</td>
                        <td class="w-half text-right">{{ $data['amount_service'] }}</td>
                    </tr>
                    <tr>
                        <td class="w-half">{{ $data['amount_add_name'] }}</td>
                        <td class="w-half text-right">{{ $data['amount_add'] }}</td>
                    </tr>
                    <tr>
                        <td class="w-half">Diskon {{ $data['discount_name'] }}</td>
                        <td class="w-half text-right">{{ $data['discount_price'] }}</td>
                    </tr>
                </table>
            </div>

            <div class="flex justify-content-center w-full" style="margin-top: 0px">
                <p>============================================</p>
            </div>

            <div class="flex justify-content-center w-full" style="margin-top: 0px">
                <table style="width: 400px">
                    <tr>
                        <td class="w-half">Total : </td>
                        <td class="w-half text-right">{{ $data['total'] }}</td>
                    </tr>
                    <tr>
                        <td class="w-half">Pembayaran : </td>
                        <td class="w-half text-right">{{ $data['payment_method'] }}</td>
                    </tr>
                </table>
            </div>

            <div class="flex justify-content-center w-full" style="margin-top: 20px">
                <h3 class="text-center">Terima Kasih</h3>
            </div>
        </div>
    </div>
</body>

</html>