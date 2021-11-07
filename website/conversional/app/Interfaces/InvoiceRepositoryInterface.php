<?php

namespace App\Interfaces;

interface InvoiceRepositoryInterface
{
    public function getUserEmailsByInvoiceId($invoiceId);
    public function getInvoicesOfSelectedUsers($invoiceId, $userEmailsSlice);
    public function getTotalCustomerPrice($invoiceId);
    public function getInvoicesByInvoiceId($invoiceId, $pagination);
    public function getEventFrequency($invoiceId);
    public function getInvoicesBetweenStartAndEnd($customerId, $startDate, $endDate);
    public function checkInvoiceExist($customerId, $startDate, $endDate);
    public function createNewInvoice($customerId, $startDate, $endDate);
    public function getUsersInInvoice($invoiceId);
    public function getInitInvoices($usersInInvoice, $customerId, $startDate);
    public function getInvoicesOrderByEventOrderNumberAndDate($invoiceId);
    public function updateInvoiceStatusToDone($invoiceId);
    public function getInvoiceStatus($invoiceId);
}
