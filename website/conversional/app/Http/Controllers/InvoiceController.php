<?php

namespace App\Http\Controllers;

use App\Http\Requests\InvoiceGetRequest;
use App\Http\Requests\InvoicePostRequest;
use App\Interfaces\InvoiceServiceInterface;

class InvoiceController extends Controller
{
    private $invoiceService;
    public function __construct(InvoiceServiceInterface $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function createInvoice(InvoicePostRequest $req)
    {
        return response()->json(['invoiceId' => $this->invoiceService->createInvoice(
            $req->customer_id,
            $req->start,
            $req->end
        )]);
    }

    public function getInvoice(InvoiceGetRequest $req)
    {
        return response()->json($this->invoiceService->getInvoice(
            $req->id,
            [
                'userLimit'     => $req->userLimit ?? Pagination::INVOICE_DEFAULT_USER_LIMIT,
                'userOffset'    => $req->userOffset ?? Pagination::INVOICE_DEFAULT_USER_OFFSET,
                'invoiceLimit'  => $req->invoiceLimit ?? Pagination::INVOICE_DEFAULT_LIMIT,
                'invoiceOffset' => $req->invoiceOffset ?? Pagination::INVOICE_DEFAULT_OFFSET,
            ],
        ));
    }
}
