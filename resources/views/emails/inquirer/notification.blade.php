@component('mail::message', ['custom_header_logo' => $data['custom_logo'] ?? ''])

<h1 class="content-header">{{$data['title'] ?? ''}}</h1>

{!! $data['message'] ?? '' !!}

@endcomponent
