<?php

namespace App\Interfaces;

interface InvoicePriceStrategyInterface
{
    public function run();
    public function setStrategy($name);
    public function setData($invoiceDetail, $invoiceUsersData, $invoiceInitUsersData);
}
