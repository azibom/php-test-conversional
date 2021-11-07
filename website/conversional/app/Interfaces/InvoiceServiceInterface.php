<?php

namespace App\Interfaces;

interface InvoiceServiceInterface
{
    public function createInvoice($customerId, $startDate, $endDate);
    public function getInvoice($invoiceId, $pagination);
    public function createInvoiceSchema($invoiceId, $customerId, $startDate, $endDate);
}
