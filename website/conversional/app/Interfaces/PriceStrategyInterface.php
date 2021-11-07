<?php

namespace App\Interfaces;

interface PriceStrategyInterface
{
    public function run();
    public function set($invoiceDetail, $invoiceUsersData, $invoiceInitUsersData);
}
