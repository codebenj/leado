<table>
  <tr>
      <th colspan="6" style="text-align:center;">Organisation Status Breakdown</th>
  </tr>
  <tr>
      <th>Organisation</th>
      <th>Total Leads</th>
      <th>Status</th>
      <th>Lead Count</th>
      <th>As %</th>
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
      <td valign="top">{{ $value->lead_count }}</td>
      <td valign="top">{{ $value->status }}</td>
      <td valign="top">
        @if($value->status == 'Won')
        {{ $value->won_count }}
        @elseif($value->status == 'Lost')
        {{ $value->lost_count }}
        @else
        {{ $value->unallocated_count }}
        @endif
      </td>
      <td valign="top">{{ $value->percent }}</td>
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
