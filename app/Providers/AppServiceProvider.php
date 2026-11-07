<?php

namespace App\Providers;

use App\Listeners\AuthAuditSubscriber;
use App\Listeners\RecordLastLoginAt;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Enquiry;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Observers\ModelAuditObserver;
use App\Policies\AuditLogPolicy;
use App\Policies\UserPolicy;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(AuditLog::class, AuditLogPolicy::class);

        Event::listen(Login::class, RecordLastLoginAt::class);
        Event::subscribe(AuthAuditSubscriber::class);

        Product::observe(ModelAuditObserver::class);
        Enquiry::observe(ModelAuditObserver::class);
        User::observe(ModelAuditObserver::class);
        Category::observe(ModelAuditObserver::class);
        Supplier::observe(ModelAuditObserver::class);
    }
}
