<?php

namespace App\Services;

use App\Interfaces\InvoiceServiceInterface;
use Illuminate\Support\Facades\Cache;


class InvoiceServiceWithCache implements InvoiceServiceInterface
{
    private $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function getInvoice($invoiceId, $pagination)
    {
        $key = sha1(json_encode([
            'pagination' => $pagination,
            'invoiceId' => $invoiceId,
        ]));

        $value = Cache::remember($key, 86400, function () use ($invoiceId, $pagination) {
            return $this->invoiceService->getInvoice($invoiceId, $pagination);
        });

        return $value;
    }

    public function createInvoice($customerId, $startDate, $endDate)
    {
        return $this->invoiceService->createInvoice($customerId, $startDate, $endDate);
    }

    public function createInvoiceSchema($invoiceId, $customerId, $startDate, $endDate)
    {
        return $this->invoiceService->createInvoiceSchema($invoiceId, $customerId, $startDate, $endDate);
    }
}
