<?php

namespace App\Services;

use App\Interfaces\InvoiceRepositoryInterface;
use App\Interfaces\InvoiceServiceInterface;
use App\Jobs\CreateInvoiceSchema;
use App\Models\InvoiceDetail;
use Exception;
use Illuminate\Support\Facades\DB;
use Throwable;

class InvoiceService implements InvoiceServiceInterface
{
    private $invoiceRepository;
    private $invoiceDetail;
    private $createInvoiceSchema;

    public function __construct(InvoiceRepositoryInterface $invoiceRepository, InvoiceDetail $invoiceDetail, CreateInvoiceSchema $createInvoiceSchema = null)
    {
        $this->invoiceRepository   = $invoiceRepository;
        $this->invoiceDetail       = $invoiceDetail;
        $this->createInvoiceSchema = $createInvoiceSchema ?? CreateInvoiceSchema::class;
    }

    public function getInvoice($invoiceId, $pagination)
    {
        $userOffset    = $pagination['userOffset'];
        $userLimit     = $pagination['userLimit'];
        $invoiceOffset = $pagination['invoiceOffset'];
        $invoiceLimit  = $pagination['invoiceLimit'];

        $eventFrequency          = $this->invoiceRepository->getEventFrequency($invoiceId);
        $userEmails              = $this->invoiceRepository->getUserEmailsByInvoiceId($invoiceId);
        $userEmailsSlice         = array_slice($userEmails->toArray(), $userOffset, $userLimit);
        $invoicesOfSelectedUsers = $this->invoiceRepository->getInvoicesOfSelectedUsers($invoiceId, $userEmailsSlice);
        $totalCustomerPrice      = $this->invoiceRepository->getTotalCustomerPrice($invoiceId)->totalPrice;
        $invoiceStatus           = $this->invoiceRepository->getInvoiceStatus($invoiceId);

        $invoices = $this->invoiceRepository->getInvoicesByInvoiceId($invoiceId, [
            'limit'  => $invoiceLimit,
            'offset' => $invoiceOffset,
        ]);

        $users = $this->formatUserInvoices($invoicesOfSelectedUsers, [
            'offset'    => $userOffset,
            'limit'     => $userLimit,
        ]);

        return [
            'invoiceStatus'  => $invoiceStatus,
            'totalPrice'     => round($totalCustomerPrice, 2),
            'eventFrequency' => $eventFrequency,
            'users'          => $users,
            'invoices'       => [
                'pagination' => [
                    'offset' => $invoiceOffset,
                    'limit'  => $invoiceLimit,
                ],
                'data' => $invoices,
            ],
        ];
    }

    public function createInvoice($customerId, $startDate, $endDate)
    {
        $invoice = $this->invoiceRepository->checkInvoiceExist($customerId, $startDate, $endDate);
        if (empty($invoice)) {
            $invoice = $this->invoiceRepository->createNewInvoice($customerId, $startDate, $endDate);
        }

        $this->createInvoiceSchema::dispatch($invoice->id, $customerId, $startDate, $endDate)->onQueue('createInvoiceSchema');

        return $invoice->id;
    }

    public function createInvoiceSchema($invoiceId, $customerId, $startDate, $endDate)
    {
        DB::beginTransaction();
        try {
            $invoices       = $this->invoiceRepository->getInvoicesBetweenStartAndEnd($customerId, $startDate, $endDate);
            $invoiceDetails = $this->checkAndFormatInvoiceDate($invoices, $invoiceId, $startDate, $endDate);
            $this->invoiceDetail::insert($invoiceDetails);
            DB::commit();
        } catch (Throwable $th) {
            DB::rollback();
            throw new Exception($th->getMessage());
        }

        return $invoiceId;
    }

    private function checkAndFormatInvoiceDate($invoices, $invoiceId, $startDate, $endDate)
    {
        $registration = [];
        $activated    = [];
        $appointment  = [];

        foreach ($invoices as $invoice) {
            if ($invoice->created > $startDate && $invoice->created < $endDate)
                $registration[$invoice->email] = [
                    'userEmail'        => $invoice->email,
                    'invoice_id'       => $invoiceId,
                    'eventName'        => "registration",
                    "eventOrderNumber" => 1,
                    'date'             => $invoice->created,
                ];

            if ($invoice->activated > $startDate && $invoice->activated < $endDate)
                if (!is_null($invoice->activated)) {
                    $activated[] = [
                        'userEmail'        => $invoice->email,
                        'invoice_id'       => $invoiceId,
                        'eventName'        => "activated",
                        "eventOrderNumber" => 2,
                        'date'             => $invoice->activated,
                    ];
                }

            if ($invoice->appointment > $startDate && $invoice->appointment < $endDate)
                if (!is_null($invoice->appointment)) {
                    $appointment[] = [
                        'userEmail'        => $invoice->email,
                        'invoice_id'       => $invoiceId,
                        'eventName'        => "appointment",
                        "eventOrderNumber" => 3,
                        'date'             => $invoice->appointment,
                    ];
                }
        }

        return array_merge(array_values($registration), array_merge($activated, $appointment));
    }

    private function formatUserInvoices($invoicesOfSelectedUsers, $pagination)
    {
        $users = [];

        foreach ($invoicesOfSelectedUsers as $invoice) {
            if (!isset($users[$invoice->userEmail])) {
                $users[$invoice->userEmail] = [];
            }

            if (!is_null($invoice->price)) {
                $users[$invoice->userEmail][] = [
                    'event'            => $invoice->eventName,
                    'price'            => $invoice->price,
                    'priceDescription' => $invoice->priceDescription,
                    'date'             => $invoice->date,
                ];
            } else {
                $users[$invoice->userEmail][] = [
                    'event' => $invoice->eventName,
                    'date'  => $invoice->date,
                ];
            }
        }

        return [
            'pagination' => [
                'offset' => $pagination['offset'],
                'limit'  => $pagination['limit'],
            ],
            'data' => $users,
        ];
    }
}
