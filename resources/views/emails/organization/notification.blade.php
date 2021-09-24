@component('mail::message', ['custom_header_logo' => $data['custom_logo'] ?? ''])

<h1 class="content-header">{{$data['title'] ?? ''}}</h1>

{!! $data['message'] ?? '' !!}

@if(isset($data['lead_id']))
<b>{{ trans('email.lead_id') }}: </b> {{ $data['lead_id'] ?? '---'}} <br />
@endif

@if(isset($data['reason']) && !empty($data['reason']))
<b>{{ trans('email.reason') }}: </b> {{ $data['reason'] ?? '---'}} <br />
@endif

@if(isset($data['comments']) && isset($data['comments']) && !empty($data['comments']))
<b>{{ trans('email.comments') }}: </b> {{ $data['comments'] ?? '---'}}  <br />
@endif

@if(isset($data['inquirer']) && isset($data['inquirer']['name']) && !empty($data['inquirer']['name']))
<b>{{ trans('email.inquirer') }}</b> <br />
  {{ trans('email.inquirer_name') }}: {{ $data['inquirer']['name'] }} <br />
  {{ trans('email.inquirer_address') }}: {{ $data['inquirer']['address'] }} <br />
  @if(!empty($data['inquirer']['email']))
  {{ trans('email.inquirer_email') }}: {{ $data['inquirer']['email'] }} <br />
  @endif
@endif

@if(isset($data['installed']) && isset($data['installed']['meters_gutter_edge']) && !empty($data['installed']['meters_gutter_edge']))
<b>{{ trans('email.installed') }}</b> <br />
{{ trans('email.meters_gutter_edge') }}: {{ $data['installed']['meters_gutter_edge'] }} <br />
{{ trans('email.meters_valley') }}: {{ $data['installed']['meters_valley'] }} <br />
@endif

@if(isset($data['date_installed']) && !empty($data['date_installed']))
<b>{{ trans('email.date_installed') }}</b> <br />
{{ trans('email.date_installed') }}: {{ $data['date_installed'] }} <br />
@endif

@if(isset($data['lead_history_link']) && $data['lead_history_link'])
@component('mail::button', ['link' => $data['lead_history_link']])
{{ trans('email.lead_update_button') }}
@endcomponent
<br />
{{ trans('email.lead_update_message') }}
@endif

@if(isset($data['login_link']) && $data['login_link'])
@component('mail::button', ['url' => $data['login_link']])
{{ trans('email.login_button') }}
@endcomponent
@endif

@endcomponent

