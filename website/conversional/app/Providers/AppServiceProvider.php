<?php

namespace App\Providers;

use App\Interfaces\InvoicePriceServiceInterface;
use App\Interfaces\InvoicePriceStrategyInterface;
use App\Interfaces\InvoiceRepositoryInterface;
use App\Interfaces\InvoiceServiceInterface;
use App\Repositories\InvoiceRepository;
use App\Services\InvoicePriceService;
use App\Services\InvoiceService;
use App\Services\InvoiceServiceWithCache;
use App\Strategies\InvoicePriceStrategy;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // $this->app->bind(InvoiceServiceInterface::class, InvoiceServiceWithCache::class);
        $this->app->bind(InvoiceServiceInterface::class, InvoiceService::class);
        $this->app->bind(InvoiceRepositoryInterface::class, InvoiceRepository::class);
        $this->app->bind(InvoicePriceServiceInterface::class, InvoicePriceService::class);
        $this->app->bind(InvoicePriceStrategyInterface::class, InvoicePriceStrategy::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
