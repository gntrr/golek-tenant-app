<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\Payment;
use Livewire\Component;

class OrderStatus extends Component
{
    public int $orderId;
    public ?Order $order = null;

    public function mount(int $orderId): void
    {
        $this->orderId = $orderId;
        $this->refreshData();
    }

    public function refreshData(): void
    {
        $this->order = Order::with('payments')->find($this->orderId);
    }

    public function getIsPaidProperty(): bool
    {
        return $this->order?->status === Order::STATUS_PAID;
    }

    public function getHasSettlementProperty(): bool
    {
        return $this->order && $this->order->payments
            ? $this->order->payments->contains(fn ($p) => $p->status === Payment::STATUS_SETTLEMENT)
            : false;
    }

    public function render()
    {
        return view('livewire.order-status');
    }
}
