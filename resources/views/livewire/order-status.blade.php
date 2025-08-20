<div wire:init="refreshData" wire:poll.5s="refreshData">
    <h1 class="card-title">Detail Pesanan</h1>
    <p class="mb-4">Berikut adalah detail pesanan Anda. Silakan periksa informasi pembayaran dan status pesanan.</p>
    <p>Invoice: <span class="font-semibold">{{ $order?->invoice_number }}</span></p>
    <p>Status Order: <span class="badge">{{ $order?->status }}</span></p>
    <p>Total: <span class="font-semibold">Rp {{ number_format($order?->total_amount ?? 0,0,',','.') }}</span></p>

    <div class="mt-4">
        <h3 class="font-semibold">Metode Pembayaran</h3>
        @forelse($order?->payments ?? [] as $p)
            <div class="border rounded p-3 mt-2">
                <div class="flex justify-between">
                    <div>Provider: {{ $p->provider }}</div>
                    <div class="badge">{{ $p->status }}</div>
                </div>
                <div>Amount: Rp {{ number_format($p->amount,0,',','.') }}</div>
                @if($p->provider === 'BANK_TRANSFER' && $p->va_number)
                    <div>Bank: <span class="font-semibold">{{ strtoupper($p->bank) }}</span></div>
                    <div class="flex items-center gap-2">
                        <span>VA: <span class="font-mono" id="va-{{ $p->id }}">{{ trim(chunk_split($p->va_number, 4, ' ')) }}</span></span>
                        <button type="button" class="btn btn-xs" onclick="copyText(document.getElementById('va-{{ $p->id }}').innerText, this)">Copy</button>
                    </div>
                @endif
                @if($p->paid_at)
                    <div>Dibayar pada: {{ $p->paid_at->format('d M Y H:i') }}</div>
                @endif
            </div>
            
            <!-- Guide Text untuk update status pembayaran selama 5 detik -->
            @if($p->status === 'PENDING' && $p->provider === 'BANK_TRANSFER')
                <div class="alert alert-info mt-2 text-white">Menunggu konfirmasi pembayaran oleh admin, silakan cek kembali dalam beberapa menit.</div>
            @endif
        @empty
            <div class="alert">Belum ada data pembayaran.</div>
        @endforelse
    </div>

    @php
        $hasActiveVA = $order && $order->payments
            ? $order->payments->contains(fn($p) => $p->provider === 'BANK_TRANSFER' && !empty($p->va_number) && $p->status === 'PENDING')
            : false;
    @endphp

    @if($this->isPaid && $this->hasSettlement)
        <div class="mt-4 flex gap-2">
            <a href="{{ route('home') }}" class="btn btn-success">Pembayaran Selesai</a>
        </div>
    @elseif(!$hasActiveVA)
        <div class="mt-4 flex gap-2">
            <a href="{{ route('client.payment.select', $order) }}" class="btn">Pilih/ubah metode pembayaran</a>
        </div>
    @endif
</div>
