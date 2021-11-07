<?php

namespace App\Repositories;

use App\Interfaces\InvoiceRepositoryInterface;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class InvoiceRepository implements InvoiceRepositoryInterface
{
    private $invoiceDetail;
    private $user;
    private $invoice;

    public function __construct(InvoiceDetail $invoiceDetail, User $user, Invoice $invoice)
    {
        $this->invoiceDetail = $invoiceDetail;
        $this->user = $user;
        $this->invoice = $invoice;
    }

    public function getUserEmailsByInvoiceId($invoiceId)
    {
        return $this->invoiceDetail::select(DB::raw("userEmail"))
            ->where("invoice_id", $invoiceId)
            ->groupBy('userEmail')
            ->get()
            ->pluck('userEmail');
    }

    public function getInvoicesOfSelectedUsers($invoiceId, $userEmailsSlice)
    {
        return $this->invoiceDetail::select(DB::raw("userEmail, eventName, price, priceDescription, date"))
            ->where("invoice_id", $invoiceId)
            ->whereIn('userEmail', $userEmailsSlice)
            ->orderBy('date')
            ->get();
    }

    public function getTotalCustomerPrice($invoiceId)
    {
        return $this->invoiceDetail::select(DB::raw("sum(price) as totalPrice"))
            ->where("invoice_id", $invoiceId)
            ->first();
    }

    public function getInvoicesByInvoiceId($invoiceId, $pagination)
    {
        return $this->invoiceDetail::select(DB::raw("userEmail, eventName, price, priceDescription, date"))
            ->where("invoice_id", $invoiceId)
            ->orderBy('date')
            ->limit($pagination['limit'])
            ->offset($pagination['offset'])
            ->get();
    }

    public function getEventFrequency($invoiceId)
    {
        return $this->invoiceDetail::select(DB::raw("eventName, count(*) as count"))
            ->where("invoice_id", $invoiceId)
            ->groupBy('eventName')
            ->get();
    }

    public function getInvoicesBetweenStartAndEnd($customerId, $startDate, $endDate)
    {
        return $this->user::select(DB::raw("users.email, users.created, sessions.activated, sessions.appointment"))
            ->whereRaw('(users.customer_id = ?) and (((users.created >  ?) and (users.created < ?)) or ((sessions.activated >  ?) and (sessions.activated < ?)) or ((sessions.appointment >  ?) and (sessions.appointment < ?)))', [$customerId, $startDate, $endDate, $startDate, $endDate, $startDate, $endDate])
            ->join('sessions', 'sessions.user_id', '=', 'users.id')
            ->distinct()
            ->get();
    }

    public function checkInvoiceExist($customerId, $startDate, $endDate)
    {
        return $this->invoice::where('customer_id', $customerId)
            ->where('start', $startDate)
            ->where('end', $endDate)
            ->first();
    }

    public function createNewInvoice($customerId, $startDate, $endDate)
    {
        $invoice = new Invoice();
        $invoice->customer_id = $customerId;
        $invoice->start       = $startDate;
        $invoice->end         = $endDate;
        $invoice->status      = 'processing';
        $invoice->save();

        return $invoice;
    }

    public function updateInvoiceStatusToDone($invoiceId)
    {
        $invoice = $this->invoice::find($invoiceId);
        $invoice->status = 'done';
        $invoice->save();
    }

    public function getInvoiceStatus($invoiceId)
    {
        $invoice = $this->invoice::find($invoiceId);

        if (empty($invoice))
            return 'Invoice Not Found';

        return $invoice->status;
    }

    public function getUsersInInvoice($invoiceId)
    {
        return $this->invoiceDetail::select(DB::raw("userEmail as email"))
            ->where("invoice_id", $invoiceId)
            ->groupBy('userEmail')
            ->get()
            ->pluck("email");
    }

    public function getInitInvoices($usersInInvoice, $customerId, $startDate)
    {
        $initInvoices  = [];
        if (count($usersInInvoice)) {
            $placeholder = implode(', ', array_fill(0, count($usersInInvoice), '?'));
            $initInvoices = User::select(DB::raw("users.email, users.created, sessions.activated, sessions.appointment"))
                ->whereRaw("(users.customer_id = ?) and (users.email IN ($placeholder)) and ((users.created < ?) or (sessions.activated < ?) or (sessions.appointment < ?))", [$customerId, $usersInInvoice, $startDate, $startDate, $startDate])
                ->join('sessions', 'sessions.user_id', '=', 'users.id')
                ->distinct()
                ->get();
        }

        return $initInvoices;
    }

    public function getInvoicesOrderByEventOrderNumberAndDate($invoiceId)
    {
        return $this->invoiceDetail::where("invoice_id", $invoiceId)
            ->orderBy('date')
            ->orderBy('eventOrderNumber')
            ->get();
    }
}
