<?php

namespace App\Jobs;

use App\Interfaces\InvoiceServiceInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateInvoiceSchema implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $invoiceId;
    private $customerId;
    private $startDate;
    private $endDate;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($invoiceId, $customerId, $startDate, $endDate)
    {
        $this->invoiceId  = $invoiceId;
        $this->customerId = $customerId;
        $this->startDate  = $startDate;
        $this->endDate    = $endDate;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(InvoiceServiceInterface $invoiceService)
    {
        $invoiceId = $invoiceService->createInvoiceSchema($this->invoiceId, $this->customerId, $this->startDate, $this->endDate);
        ProcessInvoicePrices::dispatch($invoiceId, $this->customerId, $this->startDate, $this->endDate)->onQueue('processInvoicePrices');
    }
}
