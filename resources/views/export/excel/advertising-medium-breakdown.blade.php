<table>
  <tr>
      <th colspan="6" style="text-align:center;">Advertising Medium Breakdown</th>
  </tr>
  <tr>
      <th>Medium</th>
      <th>Medium Count</th>
      <th>As %</th>
      <th>States</th>
  </tr>
  <?php
  $total = 0;
  ?>
  @foreach($data as $value)
    <tr>
      <td valign="top">{{ ucwords($value->sources) }}</td>
      <td valign="top">{{ $value->count_source }}</td>
      <td valign="top">{{ $value->percentage }}</td>
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
