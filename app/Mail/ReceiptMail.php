<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order->loadMissing(['items.booth','event']);
    }

    public function build()
    {
        return $this->subject('Receipt Pembayaran '.$this->order->invoice_number)
            ->view('emails.receipt');
    }
}
