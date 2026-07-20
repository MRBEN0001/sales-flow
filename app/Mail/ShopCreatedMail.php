<?php

namespace App\Mail;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ShopCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Tenant $tenant;
    public string $loginUrl;
    public bool $onTrial;
    public string $adminName;
    public bool $forCompany;

    public function __construct(
        Tenant $tenant,
        string $loginUrl,
        bool $onTrial,
        string $adminName,
        bool $forCompany = false
    ) {
        $this->tenant = $tenant;
        $this->loginUrl = $loginUrl;
        $this->onTrial = $onTrial;
        $this->adminName = $adminName;
        $this->forCompany = $forCompany;
    }

    public function build()
    {
        $appName = config('app.name');
        $shopName = $this->tenant->shop_name ?: $this->tenant->id;

        $subject = $this->forCompany
            ? "{$appName}: new shop registered — {$shopName}"
            : "{$appName}: your shop is ready";

        return $this->subject($subject)
            ->view('emails.shop-created');
    }
}
