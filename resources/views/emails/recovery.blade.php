@component('mail::message')
# Pending Payments

{{ count($data) }} Customer Payments in Pending.


@component('mail::table')
| Customer Name | Due Date      | Left Days| Price    |
| ------------- |:-------------:| --------:| --------:|
@foreach ($data as $payment)
| {{ $payment->customer_name }}     | {{ $payment->due_date }}  | {{ $payment->days_left }}   | {{ $payment->balance }}      |
@endforeach
@endcomponent

@component('mail::button', ['url' => 'http://localhost:3000/payments'])
    Payments
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
