<?php

namespace App\Providers;

use App\Repositories\Transfers\EloquentTransferStore;
use App\Repositories\Transfers\BaseTransferRepository;
use App\Services\Transfers\TransferService;
use App\Services\Transfers\TransferServiceInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TransferServiceInterface::class, TransferService::class);
        $this->app->bind(BaseTransferRepository::class, EloquentTransferStore::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
