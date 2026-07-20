<?php

namespace App\Mail;

use App\Models\SubscriptionPayment;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubscriptionActivatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Tenant $tenant;
    public SubscriptionPayment $payment;
    public string $planLabel;
    public string $loginUrl;
    public bool $forCompany;

    public function __construct(
        Tenant $tenant,
        SubscriptionPayment $payment,
        string $planLabel,
        string $loginUrl,
        bool $forCompany = false
    ) {
        $this->tenant = $tenant;
        $this->payment = $payment;
        $this->planLabel = $planLabel;
        $this->loginUrl = $loginUrl;
        $this->forCompany = $forCompany;
    }

    public function build()
    {
        $shop = $this->tenant->shop_name ?: $this->tenant->id;
        $subject = $this->forCompany
            ? config('app.name').": payment received — {$shop}"
            : config('app.name').': subscription activated';

        return $this->subject($subject)
            ->view('emails.subscription-activated');
    }
}
