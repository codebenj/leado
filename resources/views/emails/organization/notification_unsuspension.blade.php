@component('mail::message', ['custom_header_logo' => $data['custom_logo'] ?? ''])

<h1 class="content-header">{{ $data['title'] ?? '' }}</h1>

{!! $data['message'] ?? '' !!}

@if(isset($data['login_link']) && $data['login_link'])
@component('mail::button', ['url' => $data['login_link']])
{{ trans('email.login_button') }}
@endcomponent
@endif

@endcomponent

