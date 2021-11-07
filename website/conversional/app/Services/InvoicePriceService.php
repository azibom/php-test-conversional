<?php

namespace App\Services;

use App\Interfaces\InvoicePriceServiceInterface;
use App\Interfaces\InvoicePriceStrategyInterface;
use App\Interfaces\InvoiceRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\DB;
use Throwable;

class InvoicePriceService implements InvoicePriceServiceInterface
{
    private $invoiceRepository;
    private $invoicePriceStrategy;
    public function __construct(InvoiceRepositoryInterface $invoiceRepository, InvoicePriceStrategyInterface $invoicePriceStrategy)
    {
        $this->invoiceRepository = $invoiceRepository;
        $this->invoicePriceStrategy = $invoicePriceStrategy;
    }

    public function processInvoicePrices($invoiceId, $customerId, $startDate, $endDate)
    {
        $usersInInvoice = $this->invoiceRepository->getUsersInInvoice($invoiceId);
        $initInvoices   = $this->invoiceRepository->getInitInvoices($usersInInvoice, $customerId, $startDate);
        $invoices       = $this->invoiceRepository->getInvoicesOrderByEventOrderNumberAndDate($invoiceId);
        $invoiceInit    = $this->calcInvoiceInit($initInvoices, $startDate, $endDate);

        $invoiceUsersData = [
            'registration' => [],
            'activated'    => [],
            'appointment'  => [],
        ];

        DB::beginTransaction();
        try {
            foreach ($invoices as $invoiceDetail) {
                $this->invoicePriceStrategy->setStrategy($invoiceDetail->eventName);
                $this->invoicePriceStrategy->setData($invoiceDetail, $invoiceUsersData, $invoiceInit);
                $invoiceUsersData = $this->invoicePriceStrategy->run();
            }

            $this->invoiceRepository->updateInvoiceStatusToDone($invoiceId);
            DB::commit();
        } catch (Throwable $th) {
            DB::rollback();
            throw new Exception($th->getMessage());
        }

        return $invoiceId;
    }

    public function calcInvoiceInit($initInvoices, $startDate, $endDate)
    {
        $initRegistration = [];
        $initActivated    = [];
        $initAppointment  = [];

        foreach ($initInvoices as $value) {
            if ($value->created < $startDate)
                $initRegistration[$value->email] = true;

            if ($value->activated > $startDate && $value->activated < $endDate)
                if (!is_null($value->activated)) {
                    $initActivated[$value->email] = true;
                }

            if ($value->appointment > $startDate && $value->appointment < $endDate)
                if (!is_null($value->appointment)) {
                    $initAppointment[$value->email] = true;
                }
        }

        return [
            'initRegistration' => $initRegistration,
            'initActivated' => $initActivated,
            'initAppointment' => $initAppointment,
        ];
    }
}
