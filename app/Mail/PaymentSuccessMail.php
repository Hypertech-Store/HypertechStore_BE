<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentSuccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public $mailData; // Khai báo biến công khai
    public $orderData;

    /**
     * Create a new message instance.
     *
     * @param array $mailData
     */
    public function __construct(array $mailData)
    {
        // Gán dữ liệu
        $this->mailData = $mailData;
        $this->orderData = $mailData; // Bạn có thể gán mailData vào orderData nếu cần
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        return $this->subject('Thanh toán thành công')
            ->view('emails.payment_success')
            ->with('mailData', $this->mailData); // Chuyển mailData vào view
    }
}
