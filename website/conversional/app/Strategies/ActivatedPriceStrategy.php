<?php

namespace App\Strategies;

use App\Models\Invoice;
use App\Interfaces\PriceStrategyInterface;

class ActivatedPriceStrategy implements PriceStrategyInterface
{
    private $invoiceDetail;

    private $initRegistration = [];
    private $initActivated    = [];
    private $initAppointment  = [];
    private $registration     = [];
    private $activated        = [];
    private $appointment      = [];

    public function set($invoiceDetail, $invoiceUsersData, $invoiceInitUsersData)
    {
        $this->invoiceDetail = $invoiceDetail;

        $this->registration = $invoiceUsersData['registration'];
        $this->activated    = $invoiceUsersData['activated'];
        $this->appointment  = $invoiceUsersData['appointment'];

        $this->initRegistration = $invoiceInitUsersData['initRegistration'];
        $this->initActivated    = $invoiceInitUsersData['initActivated'];
        $this->initAppointment  = $invoiceInitUsersData['initAppointment'];
    }

    public function run()
    {
        if (
            !(isset($this->registration[$this->invoiceDetail->userEmail]) || isset($this->initRegistration[$this->invoiceDetail->userEmail])) &&
            !(isset($this->activated[$this->invoiceDetail->userEmail]) ||    isset($this->initActivated[$this->invoiceDetail->userEmail])) &&
            !(isset($this->appointment[$this->invoiceDetail->userEmail]) ||  isset($this->initAppointment[$this->invoiceDetail->userEmail]))
        ) {
            $this->invoiceDetail->price = Invoice::ACTIVATED_PRICE;
            $this->invoiceDetail->priceDescription = Invoice::PRICE_DESCRIPTION_ACTIVATED;
            $this->invoiceDetail->save();
            $this->activated[$this->invoiceDetail->userEmail] = true;
        }

        if (
            (isset($this->registration[$this->invoiceDetail->userEmail]) || isset($this->initRegistration[$this->invoiceDetail->userEmail])) &&
            !(isset($this->activated[$this->invoiceDetail->userEmail]) || isset($this->initActivated[$this->invoiceDetail->userEmail])) &&
            !(isset($this->appointment[$this->invoiceDetail->userEmail]) || isset($this->initAppointment[$this->invoiceDetail->userEmail]))
        ) {
            $this->invoiceDetail->price = Invoice::ACTIVATED_PRICE - Invoice::REGISTRATION_PRICE;
            $this->invoiceDetail->priceDescription = Invoice::PRICE_DESCRIPTION_FROM_REGISTRATION_TO_ACTIVATED;
            $this->invoiceDetail->save();
            $this->activated[$this->invoiceDetail->userEmail] = true;
        }

        return [
            'registration' => $this->registration,
            'activated'    => $this->activated,
            'appointment'  => $this->appointment,
        ];
    }
}
