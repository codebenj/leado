<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>traleado - Advertising Medium Breakdown</title>
    <style>
        table {
            border-collapse: separate;
            border-spacing: 0;
            color: #4a4a4d;
            width: 100%;
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            }
        th {
            color: #495057;
            background-color: #E9ECEF;
            border-color: #DEE2E6;
            padding: 10px 15px;
            vertical-align: middle;
            text-align: left;
            font-size: 14px/1.4;
        }
        td {
            border-bottom: 1px solid #cecfd5;
            border-right: 1px solid #cecfd5;
            vertical-align: middle;
            text-align: left;
            padding: 10px 15px;
            font-size: 14px/1.4;
            }
        td:first-child {
            border-left: 1px solid #cecfd5;
        }
        .text-center {
            text-align: center;
            margin-bottom: 3rem;
        }
        h2 {
            font-size: 20px;
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        }
    </style>
</head>
<body>
    <div class="text-center">
        <h2>Traleado</h2>
    </div>
    <table>
      <tr>
          <th colspan="4" style="text-align:center;">Advertising Medium Breakdown</th>
      </tr>
      <tr>
          <th style="text-align: center">Medium</th>
          <th style="text-align: center">Medium Count</th>
          <th style="text-align: center">As %</th>
          <th style="text-align: center">States</th>
      </tr>
      <?php
  $total = 0;
  ?>
      @foreach($data as $value)
        <tr>
          <td valign="top">{{ ucwords($value->sources) }}</td>
          <td valign="top" style="text-align: center">{{ $value->count_source }}</td>
          <td valign="top" style="text-align: center">{{ $value->percentage }}</td>
          <td valign="top">{{ $value->states }}</td>
        </tr>
        <?php
    $total += $value->count_source ;
    ?>
      @endforeach
      <tr>
        <td valign="top">Totals</td>
        <td valign="top" style="text-align: center">{{ $total }}</td>
        <td valign="top"></td>
        <td valign="top"></td>
      </tr>
    </table>
</body>
</html>
