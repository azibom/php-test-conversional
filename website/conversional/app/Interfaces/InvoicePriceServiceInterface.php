<?php

namespace App\Interfaces;

interface InvoicePriceServiceInterface
{
    public function processInvoicePrices($invoiceId, $customerId, $startDate, $endDate);
    public function calcInvoiceInit($initInvoices, $startDate, $endDate);
}
