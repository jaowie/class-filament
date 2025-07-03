<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        @page {
            size: A4 landscape;
        }

        main {
            padding: 9px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
        }

        .date {
            display: flex;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        td {
            border: 1px solid black;
            padding: 5px;
        }

        th {
            border: 1px solid black;
            padding: 5px;
        }
    </style>
</head>

<body>
    <main>
        <header>
            <div>
                <p>Name of Establishment: <strong>MALAGOS SLAUGHTERHOUSE</strong></p>
                <p>Address: <strong>PUROK 3A - BRGY.MALAGOS, BAGUIO DISTRICT, DAVAO CITY</strong></p>
            </div>
            <p>Date: {{$date}}</p>
        </header>
        <table>
            <thead>
                <tr>
                    <th rowspan="2">NO.</th>
                    <th rowspan="2">NAMES OF HOG OWNER</th>
                    <th rowspan="2">NO. OF HOGS ENTRY</th>
                    <th rowspan="2">CODE/ TATTOO NO.</th>
                    <th rowspan="2">LIVE WEIGHT</th>
                    <th rowspan="2">TIME SLAUGHTERED</th>
                    <th colspan="2">CONDEMNED PARTS</th>
                    <th rowspan="2">CARCASS WEIGHT</th>
                    <th rowspan="2">OFFAL STATUS</th>
                    <th rowspan="2">MEAT INSPECTION NOS. (MIC)</th>
                    <th rowspan="2">TIME DISPATCHED</th>
                    <th rowspan="2">MTV PLATE NO./DRIVER</th>
                    <th rowspan="2">REMARKS</th>
                </tr>
                <tr>
                    <th>VOLUME (KG)</th>
                    <th>CAUSES OF CONDEMNATION</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($owners as $owner)
                <tr>
                    <td>?</td>
                    <td>{{ $owner->first_name . ' ' . $owner->last_name }}</td>
                    <td>{{ $owner->livestock->count() }}</td>
                    <td>{{ isset($owner->livestock[0]) ? substr($owner->livestock[0]->code, 0, -4) : '' }}</td>
                    <td>{{ $owner->livestock->sum('live_weight') }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td> {{isset($owner->livestock[0]) && $owner->livestock[0]->handler ? $owner->livestock[0]->handler->plate_no : 'No handler' }}</td>
                    <td></td>
                </tr>
                @endforeach
                <tr>
                    <td colspan="14" style="text-align: left;"><strong>TOTAL NO. OF CARCASSES PRODUCED</strong></td>
                </tr>
            </tbody>
        </table>
        <div style="display: table; width: 100%;">
            <div style="display: table-cell; width: 50%; vertical-align: top;">
                <p><strong>Submitted by:</strong></p>
                <p>_______________________________________________</p>
                <p style="margin-left: 35px;"><strong>Name & Signature of Quality Control Officer</strong></p>
            </div>
            <div style="display: table-cell; width: 50%; vertical-align: top; text-align: right;">
                <p style="margin-right: 250px;"><strong>Approved:</strong></p>
                <p>_______________________________________________</p>
                <p style="margin-right: 55px;"><strong>Name & Signature of Operation Head</strong></p>
            </div>
        </div>
    </main>
</body>

</html>