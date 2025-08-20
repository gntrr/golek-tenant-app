@extends('layouts.client')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h2 class="card-title">Upload Bukti Transfer</h2>
            <p>Invoice: <span class="font-semibold">{{ $order->invoice_number }}</span></p>
            <p>Total: <span class="font-semibold">Rp {{ number_format($order->total_amount,0,',','.') }}</span></p>

            @if($fallbackBanner)
                <div class="alert alert-warning mt-2">{!! nl2br(e($fallbackBanner)) !!}</div>
            @endif

            @if($instructions)
                <div class="alert mt-2">{!! nl2br(e($instructions)) !!}</div>
            @endif

            @isset($payment)
                <div class="mt-4 border rounded p-3">
                    <div class="font-semibold mb-2">Virtual Account</div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>Bank</div>
                        <div class="font-mono">{{ strtoupper($payment->bank ?? '-') }}</div>
                        <div>VA Number</div>
                        <div class="flex items-center gap-2">
                            <span class="font-mono" id="va-value">{{ $payment->va_number ?? '-' }}</span>
                            @if(!empty($payment->va_number))
                                <button type="button" class="btn btn-xs" onclick="copyText(document.getElementById('va-value').innerText, this)">Copy</button>
                            @endif
                        </div>
                    </div>
                </div>
                <form method="POST" action="{{ route('client.payment.upload.changeBank', $order) }}" class="mt-2 flex items-end gap-2">
                    @csrf
                    <div>
                        <label class="label"><span class="label-text">Ganti Bank VA</span></label>
                        <select name="bank" class="select select-bordered" required>
                            @php($banks = ['bca'=>'BCA','bni'=>'BNI','bri'=>'BRI','permata'=>'Permata'])
                            @foreach($banks as $code=>$name)
                                <option value="{{ $code }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="btn">Perbarui VA</button>
                </form>
            @endisset

            <form method="POST" action="{{ route('client.payment.upload.store', $order) }}" enctype="multipart/form-data" class="mt-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label"><span class="label-text">Pilih Bank VA</span></label>
                        <select name="bank" class="select select-bordered w-full" required>
                            @php($banks = ['bca'=>'BCA','bni'=>'BNI','bri'=>'BRI','permata'=>'Permata'])
                            @foreach($banks as $code=>$name)
                                <option value="{{ $code }}" @selected(($payment->bank ?? old('bank')) === $code)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label"><span class="label-text">Bukti Transfer</span></label>
                        <input type="file" name="proof" class="file-input file-input-bordered w-full" accept="image/png,image/jpeg,application/pdf" required />
                        @error('proof')<div class="text-error text-sm mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="mt-4 flex justify-end">
                    <button class="btn btn-primary">Kirim</button>
                </div>
            </form>
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
</script>
@endsection