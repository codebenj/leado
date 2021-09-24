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
          <th colspan="6" style="text-align:center;">Organisation Status Breakdown</th>
      </tr>
      <tr>
          <th style="text-align: center">Organisation</th>
          <th style="text-align: center">Total Leads</th>
          <th style="text-align: center">Status</th>
          <th style="text-align: center">Lead Count</th>
          <th style="text-align: center">As %</th>
          <th>Metres WON</th>
      </tr>
      <?php
  $total_leads = 0;
  $total_leads_count = 0;
  $total_metres = 0;
  ?>
      @foreach($data as $value)
        <tr>
          <td valign="top">{{ $value->name }}</td>
          <td style="text-align: center" valign="top">{{ $value->lead_count }}</td>
          <td style="text-align: center" valign="top">{{ $value->status }}</td>
          <td style="text-align: center" valign="top">
            @if($value->status == 'Won')
        {{ $value->won_count }}
        @elseif($value->status == 'Lost')
        {{ $value->lost_count }}
        @else
        {{ $value->unallocated_count }}
        @endif
          </td>
          <td style="text-align: center" valign="top">{{ $value->percent }}</td>
          <td valign="top">
            @if($value->status == 'Won')
            <?php
          $total_metres += $value->installed_meters;
          ?>
              {{ $value->installed_meters }}
            @endif
          </td>
        </tr>
        <?php
    if($value->status == 'Won'){
      $total_leads += $value->won_count;
    }else if($value->status == 'Lost'){
      $total_leads += $value->lost_count;
    }else{
      $total_leads += $value->unallocated_count;
    }
    ?>
      @endforeach
      <tr>
        <td valign="top">Totals</td>
        <td valign="top" style="text-align: right">{{ $total_leads }}</td>
        <td valign="top"></td>
        <td valign="top"></td>
        <td valign="top"></td>
        <td valign="top" style="text-align: right">{{ $total_metres }}</td>
      </tr>
    </table>
</body>
</html>
