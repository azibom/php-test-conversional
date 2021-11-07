# php-test-conversional
### How start the project
1.Install docker and docker-compose <br>
2.Run these commands
```
# go to the root of the project where docker-compose.yml exist
docker-compose up -d
make init
make seed
```
3.Install supervisor and config it <br>
Install it
```
sudo apt-get install supervisor
```
Config it <br>
Go to the `/etc/supervisor/conf.d` and make a file with name `laravel-worker.conf` and then paste this on it
```
[program:processInvoicePrices-worker]
process_name=%(program_name)s_%(process_num)02d
command=docker exec -i lemp-php bash -c "cd /var/www/html/website/conversional;php artisan queue:work --queue=processInvoicePrices"
autostart=true
autorestart=true
user=root
startsecs=0
numprocs=8
redirect_stderr=true
stdout_logfile=/var/log/conversional-processInvoicePrices-queue.log

[program:createInvoiceSchema-worker]
process_name=%(program_name)s_%(process_num)02d
command=docker exec -i lemp-php bash -c "cd /var/www/html/website/conversional;php artisan queue:work --queue=createInvoiceSchema"
autostart=true
autorestart=true
user=root
startsecs=0
numprocs=4
redirect_stderr=true
stdout_logfile=/var/log/conversional-createInvoiceSchema-queue.log
```
Start it
```
sudo supervisorctl reread
sudo supervisorctl update
```

3.Done :sunglasses::fire::fire: <br>
Now you can use apis from this links <br> http://localhost:85/api/invoices and http://localhost:85/api/invoices/1

#### Some requst example
![Screenshot from 2021-11-07 16-03-06](https://user-images.githubusercontent.com/34370960/140645084-70e2a2eb-bff6-4687-93d8-e76d7c59a97e.png)

![Screenshot from 2021-11-07 16-03-33](https://user-images.githubusercontent.com/34370960/140645098-eb42547a-9312-4f8a-abae-5d18f4fcb70a.png)

### Run tests :fire:
I wrote some tests for the app, somethings like this ...
```
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
```
And you can run them with
```
make test
```
And that is the result
```
Invoice Service (Tests\Unit\InvoiceService)
 ✔ InvoiceService createInvoice with invoice
 ✔ InvoiceService createInvoice without invoice

Invoice Service (Tests\Feature\InvoiceService)
 ✔ Post api response
 ✔ Get api response

Time: 00:00.187, Memory: 28.00 MB

OK (4 tests, 9 assertions)
```
You can run this command for test the app with different scenarios
```
make test
```
And that will be the result
```
6 scenarios (6 passed)
33 steps (33 passed)
0m2.91s (11.53Mb)
```

## About the structure

### Multi processes
![Screenshot from 2021-11-07 16-16-27](https://user-images.githubusercontent.com/34370960/140645506-017a0980-f018-4a33-9338-ee36b24a0215.png)
We have two job and when some one call the create api first of all we send the `invoiceId` to the create invoice queue and then after that we send the `invoiceId` to the price processor job to calc the price of the raw invoices

### Caching
I also cache the result to optimize the response more
```
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
```

### Price strategy pattern
For calc the price I also use the strategy pattern to make it more flexable and more readable
```
foreach ($invoices as $invoiceDetail) {
    $this->invoicePriceStrategy->setStrategy($invoiceDetail->eventName);
    $this->invoicePriceStrategy->setData($invoiceDetail, $invoiceUsersData, $invoiceInit);
    $invoiceUsersData = $this->invoicePriceStrategy->run();
}
```
And if you want to look at one strategy you can look at this
```
public function run()
{
    if (
        !(isset($this->registration[$this->invoiceDetail->userEmail]) || isset($this->initRegistration[$this->invoiceDetail->userEmail])) &&
        !(isset($this->activated[$this->invoiceDetail->userEmail]) ||    isset($this->initActivated[$this->invoiceDetail->userEmail])) &&
        !(isset($this->appointment[$this->invoiceDetail->userEmail]) ||  isset($this->initAppointment[$this->invoiceDetail->userEmail]))
    ) {
        $this->invoiceDetail->price = Invoice::ACTIVATED_PRICE;
        $this->invoiceDetail->priceDescription = Invoice::PRICE_DESCRIPTION_ACTIVATED;
        $this->invoiceDetail->save();
        $this->activated[$this->invoiceDetail->userEmail] = true;
    }

    if (
        (isset($this->registration[$this->invoiceDetail->userEmail]) || isset($this->initRegistration[$this->invoiceDetail->userEmail])) &&
        !(isset($this->activated[$this->invoiceDetail->userEmail]) || isset($this->initActivated[$this->invoiceDetail->userEmail])) &&
        !(isset($this->appointment[$this->invoiceDetail->userEmail]) || isset($this->initAppointment[$this->invoiceDetail->userEmail]))
    ) {
        $this->invoiceDetail->price = Invoice::ACTIVATED_PRICE - Invoice::REGISTRATION_PRICE;
        $this->invoiceDetail->priceDescription = Invoice::PRICE_DESCRIPTION_FROM_REGISTRATION_TO_ACTIVATED;
        $this->invoiceDetail->save();
        $this->activated[$this->invoiceDetail->userEmail] = true;
    }

    return [
        'registration' => $this->registration,
        'activated'    => $this->activated,
        'appointment'  => $this->appointment,
    ];
}
```
### Controller, Service, Repo, DB leyers
After data go to the controller I just inject the services and controller just see the services and then services just can see the repository layer and after all of them the repo can call the database, I try to inject the dependencies with interface to make it easier to writing tests <br>
That is data flow diagram
![Screenshot from 2021-11-07 16-45-36](https://user-images.githubusercontent.com/34370960/140646513-1864de30-c44e-408a-aa9b-3f5d8db48966.png)


### Transaction
I also use transactions to make sure that I will have clean database
```
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
```
### Pagination
Because the invoice result can become vary large, I add pagination to the get api and I also put the default value for it
```
class Pagination
{
    const INVOICE_DEFAULT_USER_LIMIT = 100;
    const INVOICE_DEFAULT_USER_OFFSET = 0;
    const INVOICE_DEFAULT_LIMIT = 100;
    const INVOICE_DEFAULT_OFFSET = 0;
}
```
And you can fill them like this
```
http://localhost:85/api/invoices/3?userLimit=1&userOffset=2&invoiceLimit=3&invoiceOffset=4
```
![Screenshot from 2021-11-07 16-32-50](https://user-images.githubusercontent.com/34370960/140646043-318c325c-1be5-42a5-b268-888c344f972a.png)

I think that is all :) but I like to explain it more it our code review session <br>
