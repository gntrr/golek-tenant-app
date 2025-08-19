@extends('layouts.client')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            @livewire('order-status', ['orderId' => $order->id])
        </div>
    </div>
</div>
@endsection