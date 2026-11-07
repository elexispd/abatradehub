<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->unique();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->unsignedInteger('moq')->default(1); // minimum order quantity
            $table->string('moq_unit')->default('pieces');
            $table->decimal('base_price', 12, 2)->nullable(); // used when there are no price tiers
            $table->string('currency', 3)->default('NGN');
            $table->unsignedSmallInteger('lead_time_min_days')->nullable();
            $table->unsignedSmallInteger('lead_time_max_days')->nullable();
            $table->integer('stock_quantity')->nullable(); // null = made to order / not tracked
            $table->unsignedInteger('low_stock_threshold')->default(10);
            $table->string('status')->default('draft')->index(); // draft | published
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
