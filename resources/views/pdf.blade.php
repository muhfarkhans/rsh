<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>

    </title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"
        integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <style>
        .container {
            width: 700px;
            margin: 0 auto;
        }

        .section {
            display: flex;
            justify-content: space-between;
            margin: 10px 0px;
        }
    </style>
</head>

<body>
    <button id="topdf">generate pdf</button>

    <div id="canvas" class="container">
        <div class="section">
            <div class="">
                <div class="">
                    <h3>Keluhan saat ini: </h3>
                    <p>Lorem ipsum dolor </p>
                </div>

                <div class="" style="margin: 50px 0px">
                    <h3>Tanda Tanda Vital</h3>
                    <table>
                        <tr>
                            <td>Suhu</td>
                            <td>: </td>
                            <td>90</td>
                        </tr>
                        <tr>
                            <td>Tekanan Darah</td>
                            <td>: </td>
                            <td>100</td>
                        </tr>
                    </table>
                </div>
            </div>
            <div id="map" style="width: 350px; height: 500px; z-index: 1; border: 1px black solid"></div>
        </div>
    </div>
</body>

<script>
    // document.getElementById("topdf").addEventListener("click", function () {

    // })
    document.getElementById('topdf').onclick = function () {
        // var element = document.getElementById("canvas")
        // html2pdf().from(element).toPdf().save('my_document.pdf')

        var element = document.getElementById('canvas');
        var opt = {
            // margin: 1,
            // filename: 'myfile.pdf',
            // image: { type: 'jpeg', quality: 0.98 },
            // html2canvas: {},
            jsPDF: { orientation: 'portrait' }
        };

        html2pdf()
            .set(opt)
            .from(element)
            .outputPdf()
            .get('pdf')
            .then(function (pdfObj) {
                pdfObj.autoPrint();
                window.open(pdfObj.output("bloburl"), "F")
            });
    }

    $(document).ready(function () {
        let map;

        if (map != undefined) {
            map.off();
            map.remove();
        }

        map = L.map('map', {
            crs: L.CRS.Simple,
            minZoom: -1,
        });

        const bounds = [[0, 0], [500, 350]];
        const imageurl = '{{ url('') }}' + '/assets/images/skeleton.jpg';

        console.log("imageurl: ", imageurl);
        console.log("map: ", map);

        const image = L.imageOverlay(imageurl, bounds).addTo(map);
        map.fitBounds(bounds);
        map.removeControl(map.zoomControl);
        map.removeControl(map.attributionControl);
        map.removeControl(map.scaleControl);

        if (map.fullscreenControl) {
            map.removeControl(map.fullscreenControl);
        }
    });
</script>

</html>