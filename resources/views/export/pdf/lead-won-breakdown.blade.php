<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    {{-- <meta name="viewport" content="width=device-width, initial-scale=1.0"> --}}
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
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
        .page-break {
    page-break-after: always;
}
    </style>
</head>
<div class="text-center">
  <h2>Traleado</h2>
</div>
<table>
  <tr>
      <th colspan="9" style="text-align:center;">Leads Won Breakdown</th>
  </tr>

  <thead>
    <tr>
      <th>Medium&nbsp;</th>
      <th style="text-align: center">Total Leads</th>
      <th style="text-align: center">Leads Won</th>
      <th style="text-align: center">As %</th>
      <th style="text-align: center">State&nbsp;</th>
      <th style="text-align: center">State Total</th>
      <th style="text-align: center">% of Won</th>
      <th style="text-align: center">Est. Metres by State</th>
      <th style="text-align: center">Metres Won by State</th>
  </tr>
</thead>
  <?php
  $total_leads = 0;
  $total_lead_won = 0;
  $total_meters = 0;
  $total_actual_meters = 0;
  ?>

  @foreach($data as $value)
    <?php
      $total_leads += $value['total_leads'];
      $total_lead_won += $value['lead_won'];
      $total_meters += $value['lead_total_meters'][0];
      $total_actual_meters += $value['lead_total_meters_actual'][0];
    ?>
      <tr>
        {{-- <td valign="center" rowspan="{{ count($value['states']) == 0 ? 1 : count($value['states']) }}">&nbsp;{{ ucwords($value['medium']) }} &nbsp;</td>
        <td valign="center" style="text-align: center" rowspan="{{ count($value['states_total']) == 0 ? 1 : count($value['states_total']) }}">&nbsp;{{ $value['total_leads'] }}</td>
        <td valign="center" style="text-align: center" rowspan="{{ count($value['lead_states_won']) == 0 ? 1 : count($value['lead_states_won']) }}">&nbsp;{{ $value['lead_won'] }}</td>
        <td valign="center" style="text-align: center" rowspan="{{ count($value['lead_states_won']) == 0 ? 1 : count($value['lead_states_won']) }}">&nbsp;{{ $value['percentage_won'] }}</td> --}}
        @if(count($value['states']) == 1)
        <td valign="center" style="text-align: center;">&nbsp;{{ ucwords($value['medium']) }} &nbsp;</td>
        <td valign="center" style="text-align: center;">&nbsp;{{ $value['total_leads'] }}</td>
        <td valign="center" style="text-align: center;">&nbsp;{{ $value['lead_won'] }}</td>
        <td valign="center" style="text-align: center;">&nbsp;{{ $value['percentage_won'] }}</td>
        @else
        <td valign="center" style="text-align: center; border-bottom: 0px">&nbsp;{{ ucwords($value['medium']) }} &nbsp;</td>
        <td valign="center" style="text-align: center; border-bottom: 0px">&nbsp;{{ $value['total_leads'] }}</td>
        <td valign="center" style="text-align: center; border-bottom: 0px">&nbsp;{{ $value['lead_won'] }}</td>
        <td valign="center" style="text-align: center; border-bottom: 0px">&nbsp;{{ $value['percentage_won'] }}</td>
        @endif

        <td style="text-align: center">&nbsp;{{ $value['states'][0] }}&nbsp;</td>
        <td style="text-align: center">&nbsp;{{ $value['states_total'][0] }}</td>
        <td style="text-align: center">&nbsp;{{ $value['lead_states_won'][0] }}</td>
        <td style="text-align: center">&nbsp;{{ $value['lead_total_meters'][0] }}</td>
        <td style="text-align: center">&nbsp;{{ $value['lead_total_meters_actual'][0] }}</td>
      </tr>

    @for($i=0; $i<count($value['states']); $i++)
      @if($i !== 0)
        <?php
          $total_meters += $value['lead_total_meters'][$i];
          $total_actual_meters += $value['lead_total_meters_actual'][$i];
        ?>
        @if($i < (count($value['states']) - 1))
        <tr>
          <td style="text-align: center; border-bottom: 0px">&nbsp;&nbsp;</td>
          <td style="text-align: center; border-bottom: 0px">&nbsp;&nbsp;</td>
          <td style="text-align: center; border-bottom: 0px">&nbsp;&nbsp;</td>
          <td style="text-align: center; border-bottom: 0px">&nbsp;&nbsp;</td>
          <td style="text-align: center">&nbsp;{{ $value['states'][$i] }}&nbsp;</td>
          <td style="text-align: center">&nbsp;{{ $value['states_total'][$i] }}</td>
          <td style="text-align: center">&nbsp;{{ $value['lead_states_won'][$i] }}</td>
          <td style="text-align: center">&nbsp;{{ $value['lead_total_meters'][$i] }}</td>
          <td style="text-align: center">&nbsp;{{ $value['lead_total_meters_actual'][$i] }}</td>
        </tr>
        @else
        <tr>
          <td style="text-align: center;">&nbsp;&nbsp;</td>
          <td style="text-align: center;">&nbsp;&nbsp;</td>
          <td style="text-align: center;">&nbsp;&nbsp;</td>
          <td style="text-align: center;">&nbsp;&nbsp;</td>
          <td style="text-align: center">&nbsp;{{ $value['states'][$i] }}&nbsp;</td>
          <td style="text-align: center">&nbsp;{{ $value['states_total'][$i] }}</td>
          <td style="text-align: center">&nbsp;{{ $value['lead_states_won'][$i] }}</td>
          <td style="text-align: center">&nbsp;{{ $value['lead_total_meters'][$i] }}</td>
          <td style="text-align: center">&nbsp;{{ $value['lead_total_meters_actual'][$i] }}</td>
        </tr>
        @endif
      @endif
    @endfor



  @endforeach


  <tr>
    <td>Totals</td>
    <td style="text-align: center">{{ $total_leads }}</td>
    <td style="text-align: center">{{ $total_lead_won }}</td>
    <td></td>
    <td></td>
    <td style="text-align: center"></td>
    <td></td>
    <td style="text-align: center">{{ $total_meters }}</td>
    <td style="text-align: center">{{ $total_actual_meters }}</td>
  </tr>

</table>
</html>
