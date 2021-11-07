<?php

namespace App\Strategies;

use App\Interfaces\InvoicePriceStrategyInterface;
use App\Models\Invoice;
use Exception;

class InvoicePriceStrategy implements InvoicePriceStrategyInterface
{
    private $registrationPriceStrategy;
    private $strategy;

    public function __construct(
        RegistrationPriceStrategy $registrationPriceStrategy,
        ActivatedPriceStrategy $activatedPriceStrategy,
        AppointmentPriceStrategy $appointmentPriceStrategy
    ) {
        $this->registrationPriceStrategy = $registrationPriceStrategy;
        $this->activatedPriceStrategy = $activatedPriceStrategy;
        $this->appointmentPriceStrategy = $appointmentPriceStrategy;
    }

    public function setStrategy($name)
    {
        if ($name == Invoice::REGISTRATION)
            $this->strategy = $this->registrationPriceStrategy;
        else if ($name == Invoice::ACTIVATED)
            $this->strategy = $this->activatedPriceStrategy;
        else if ($name == Invoice::APPOINTMENT)
            $this->strategy = $this->appointmentPriceStrategy;
        else
            throw new Exception('Invalid Strategy Name!');
    }

    public function setData($invoiceDetail, $invoiceUsersData, $invoiceInitUsersData)
    {
        $this->strategy->set($invoiceDetail, $invoiceUsersData, $invoiceInitUsersData);
    }

    public function run()
    {
        return $this->strategy->run();
    }
}
