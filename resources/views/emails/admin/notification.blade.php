@component('mail::message')

<h1 class="content-header">{{$data['title'] ?? ''}}</h1>

{!! $data['message'] ?? '' !!}

@if(isset($data['lead_id']))
<b>{{ trans('email.lead_id') }}: </b> {{ $data['lead_id'] ?? '---'}} <br />
@endif

@if(isset($data['reason']) && !empty($data['reason']))
<b>{{ trans('email.reason') }}: </b> {{ $data['reason'] ?? '---'}} <br />
@endif

@if(isset($data['comments']) && !empty($data['comments']))
<b>{{ trans('email.comments') }}: </b> {{ $data['comments'] ?? '---'}}  <br />
@endif

@if(isset($data['org']) && isset($data['org']['lead_id']) && !empty($data['org']['lead_id']))
<b>{{ trans('email.org') }}</b> <br />
  {{ trans('email.lead_id') }}: {{ $data['org']['lead_id'] }} <br />
  {{ trans('email.org_name') }}: {{ $data['org']['name'] }} @if ($data['org']['manual_escalation']) <img class="m-icon" src="{{ asset('/app-assets/img/svg/group.svg') }}" alt="Manual Escalation" /> @endif <br />
  {{ trans('email.org_phone') }}: {{ $data['org']['contact_number'] }} <br />
  {{ trans('email.org_email') }}: {{ $data['org']['email'] }}
@endif

@if(isset($data['inquirer']) && isset($data['inquirer']['name']) && !empty($data['inquirer']['name']))
<b>{{ trans('email.inquirer') }}</b> <br />
  {{ trans('email.inquirer_name') }}: {{ $data['inquirer']['name'] }} <br />
  {{ trans('email.inquirer_address') }}: {{ $data['inquirer']['address'] }} <br />
  @if(!empty($data['inquirer']['email']))
  {{ trans('email.inquirer_email') }}: {{ $data['inquirer']['email'] }} <br />
  @endif
@endif

@if(isset($data['installed']) && isset($data['installed']['meters_gutter_edge']) && (strlen($data['installed']['meters_gutter_edge']) !=0 || strlen($data['installed']['meters_valley']) !=0))
<b>{{ trans('email.installed') }}</b> <br />
{{ trans('email.meters_gutter_edge') }}: {{ $data['installed']['meters_gutter_edge'] }} <br />
{{ trans('email.meters_valley') }}: {{ $data['installed']['meters_valley'] }} <br />
@endif

@if(isset($data['date_installed']) && !empty($data['date_installed']))
<b>{{ trans('email.date_installed') }}</b> <br />
{{ trans('email.date_installed') }}: {{ $data['date_installed'] }} <br />
@endif

@endcomponent
