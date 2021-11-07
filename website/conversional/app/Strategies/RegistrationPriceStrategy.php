<?php

namespace App\Strategies;

use App\Models\Invoice;
use App\Interfaces\PriceStrategyInterface;

class RegistrationPriceStrategy implements PriceStrategyInterface
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
        if (!(isset($this->registration[$this->invoiceDetail->userEmail]) || isset($this->initRegistration[$this->invoiceDetail->userEmail]))) {
            $this->invoiceDetail->price = Invoice::REGISTRATION_PRICE;
            $this->invoiceDetail->priceDescription = Invoice::PRICE_DESCRIPTION_REGISTRATION;
            $this->invoiceDetail->save();
            $this->registration[$this->invoiceDetail->userEmail] = true;
        }

        return [
            'registration' => $this->registration,
            'activated'    => $this->activated,
            'appointment'  => $this->appointment,
        ];
    }
}
