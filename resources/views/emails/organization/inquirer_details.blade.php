@component('mail::message', ['custom_header_logo' => $data['custom_logo'] ?? ''])

<h1 class="content-header">{{$data['title'] ?? ''}}</h1>


@if(isset($data['inquirer']) && isset($data['inquirer']['name']) && !empty($data['inquirer']['name']))
<br />
  Name: {{ $data['inquirer']['name'] }} <br />
  Address: {{ $data['inquirer']['address'] }} <br />
  @if(!empty($data['inquirer']['email']))
  Email: {{ $data['inquirer']['email'] }} <br />
  @endif
  @if(!empty($data['inquirer']['contact_number']))
  Contact Number: {{ $data['inquirer']['contact_number'] }} <br />
  @endif
@endif

@endcomponent

