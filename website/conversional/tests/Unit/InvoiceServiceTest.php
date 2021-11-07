<?php

namespace Tests\Unit;

use App\Interfaces\InvoiceRepositoryInterface;
use App\Jobs\CreateInvoiceSchema;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Services\InvoiceService;
use Illuminate\Foundation\Bus\PendingDispatch;
use Mockery;
use PHPUnit\Framework\TestCase;

class InvoiceServiceTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
    }

    public function test_invoiceService_createInvoice_with_invoice()
    {
        $invoiceRepositoryMock   = Mockery::mock(InvoiceRepositoryInterface::class);
        $invoiceDetailMock       = Mockery::mock(InvoiceDetail::class);
        $createInvoiceSchemaMock = Mockery::mock(CreateInvoiceSchema::class);
        $pendingDispatchMock     = Mockery::mock(PendingDispatch::class);
        $invoiceMock             = Mockery::mock(Invoice::class);

        $pendingDispatchMock->shouldReceive('onQueue');

        $createInvoiceSchemaMock->shouldReceive('dispatch')
            ->with(2, 1, '2020-10-10', '2021-10-10')
            ->andReturn($pendingDispatchMock);

        $invoiceMock->shouldReceive('getAttribute')
            ->with('id')
            ->andReturn(2);

        $invoiceRepositoryMock->shouldReceive('checkInvoiceExist')
            ->with(1, '2020-10-10', '2021-10-10')
            ->once()
            ->andReturn($invoiceMock);


        $invoiceService = new InvoiceService($invoiceRepositoryMock, $invoiceDetailMock, $createInvoiceSchemaMock);
        $id = $invoiceService->createInvoice(1, '2020-10-10', '2021-10-10');

        $this->assertEquals($id, 2);
    }

    public function test_invoiceService_createInvoice_without_invoice()
    {
        $invoiceRepositoryMock   = Mockery::mock(InvoiceRepositoryInterface::class);
        $invoiceDetailMock       = Mockery::mock(InvoiceDetail::class);
        $createInvoiceSchemaMock = Mockery::mock(CreateInvoiceSchema::class);
        $pendingDispatchMock     = Mockery::mock(PendingDispatch::class);
        $invoiceMock             = Mockery::mock(Invoice::class);

        $pendingDispatchMock->shouldReceive('onQueue');

        $createInvoiceSchemaMock->shouldReceive('dispatch')
            ->with(2, 1, '2020-10-10', '2021-10-10')
            ->andReturn($pendingDispatchMock);

        $invoiceMock->shouldReceive('getAttribute')
            ->with('id')
            ->andReturn(2);

        $invoiceRepositoryMock->shouldReceive('checkInvoiceExist')
            ->with(1, '2020-10-10', '2021-10-10')
            ->once()
            ->andReturn(null);

        $invoiceRepositoryMock->shouldReceive('createNewInvoice')
            ->with(1, '2020-10-10', '2021-10-10')
            ->once()
            ->andReturn($invoiceMock);

        $invoiceService = new InvoiceService($invoiceRepositoryMock, $invoiceDetailMock, $createInvoiceSchemaMock);
        $id = $invoiceService->createInvoice(1, '2020-10-10', '2021-10-10');

        $this->assertEquals($id, 2);
    }
}
