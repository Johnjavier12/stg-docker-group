<?php

namespace App\Mail\RealEstate;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use App\Models\RealEstate\RealEstatePayment;

class SuccessfulPaymentPayMaya extends Mailable
{
    use Queueable, SerializesModels;

    protected $payment;
    protected $mailSubject;
    protected $payload;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($payment, $mailSubject, $payload)
    {
        //
        $this->payment = $payment;
        $this->mailSubject = $mailSubject;
        $this->payload = $payload;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        return $this->subject($this->mailSubject)
                    ->from('online.payment@camayacoast.com', 'Camaya Real Estate Payment')
                    ->cc(env('APP_ENV') == 'production' ? 'online.payment@camayacoast.com' : 'kit.seno@camayacoast.com')
                    ->with([
                         'payment' => $this->payment,
                         'data' => $this->payload
                    ])
                    ->markdown('emails.realestate.successful_payment_paymaya');
    }

}
