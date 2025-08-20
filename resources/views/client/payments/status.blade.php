@extends('layouts.client')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            @livewire('order-status', ['orderId' => $order->id])
        </div>
    </div>
</div>
<script>
    function ensureToastContainer(){
        let c = document.getElementById('toast-container');
        if(!c){
            c = document.createElement('div');
            c.id = 'toast-container';
            c.className = 'toast toast-top toast-end';
            document.body.appendChild(c);
        }
        return c;
    }
    function showToast(message, type='success'){
        const c = ensureToastContainer();
        const t = document.createElement('div');
        t.className = 'alert ' + (type === 'success' ? 'alert-success' : 'alert-info');
        t.innerHTML = '<span>'+message+'</span>';
        c.appendChild(t);
        setTimeout(()=>{
            t.remove();
        }, 1600);
    }
    function copyText(text, btn){
        if(!text) return;
        navigator.clipboard.writeText(text).then(()=>{
            if(btn){
                const prev = btn.innerText;
                btn.innerText = 'Disalin';
                setTimeout(()=> btn.innerText = prev, 1200);
            }
            showToast('VA disalin ke clipboard');
        });
    }
;</script>
@endsection