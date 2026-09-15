<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('api_url');
            $table->text('api_key')->nullable();
            $table->string('status')->default('active')->index();
            $table->string('currency', 3)->default('USD');
            $table->timestamps();
        });

        Schema::create('provider_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->string('external_service_id');
            $table->string('name');
            $table->string('category')->nullable();
            $table->string('platform')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('min_quantity')->default(1);
            $table->unsignedBigInteger('max_quantity')->default(1000000);
            $table->decimal('provider_price', 15, 4)->default(0);
            $table->string('provider_currency', 3)->default('USD');
            $table->boolean('supports_refill')->default(false);
            $table->boolean('supports_cancel')->default(false);
            $table->boolean('supports_drip_feed')->default(false);
            $table->string('status')->default('active')->index();
            $table->json('raw_response')->nullable();
            $table->timestamps();

            $table->unique(['provider_id', 'external_service_id']);
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('provider_service_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('platform')->nullable();
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->decimal('base_price', 15, 4)->default(0);
            $table->decimal('selling_price', 15, 4)->default(0);
            $table->string('markup_type')->default('percentage');
            $table->decimal('markup_value', 15, 4)->default(0);
            $table->unsignedBigInteger('min_quantity')->default(1);
            $table->unsignedBigInteger('max_quantity')->default(1000000);
            $table->string('status')->default('active')->index();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('tenant_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->decimal('selling_price', 15, 4)->default(0);
            $table->string('markup_type')->default('percentage');
            $table->decimal('markup_value', 15, 4)->default(0);
            $table->string('status')->default('active')->index();
            $table->timestamps();

            $table->unique(['tenant_id', 'service_id']);
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('password');
            $table->string('status')->default('active')->index();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'email']);
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('provider_id')->nullable()->constrained()->nullOnDelete();
            $table->string('order_number')->unique();
            $table->string('provider_order_id')->nullable()->index();
            $table->text('target');
            $table->unsignedBigInteger('quantity');
            $table->decimal('provider_unit_cost', 15, 4)->default(0);
            $table->decimal('provider_total_cost', 15, 4)->default(0);
            $table->decimal('customer_unit_price', 15, 4)->default(0);
            $table->decimal('customer_total_price', 15, 4)->default(0);
            $table->decimal('profit_amount', 15, 4)->default(0);
            $table->string('status')->default('pending')->index();
            $table->string('provider_status')->nullable();
            $table->string('payment_status')->default('unpaid')->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->string('source')->default('system');
            $table->text('message')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_status_histories');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('tenant_services');
        Schema::dropIfExists('services');
        Schema::dropIfExists('provider_services');
        Schema::dropIfExists('providers');
    }
};
