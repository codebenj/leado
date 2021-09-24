@component('mail::message', ['custom_header_logo' => $data['custom_logo'] ?? ''])

<h1 class="content-header">{{$data['title'] ?? ''}}</h1>

<p>Thank you for your enquiry regarding your nearest Leaf Stopper Store. We have the following locations for you to contact.</p>

{!! $data['message'] ?? '' !!}

@foreach($data['stores'] as $store)
<p>
<b>{{$store->name}}</b><br>
{{ $store->street_address }}<br>
{{ $store->suburb }} {{ $store->postcode }} {{ $store->state }}<br>
{{ $store->phone_number }}<br>
</p><br><br>
@endforeach

<p>We trust the store will be able to help you with your questions and order. If you have any issues, we would encourage you to get in touch with us at <a href="office@leafstopper.com.au">office@leafstopper.com.au</a> or call 1300 334 333 during business hours.</p>

Regards, <br>
Leaf Stopper Team <br>
@endcomponent
