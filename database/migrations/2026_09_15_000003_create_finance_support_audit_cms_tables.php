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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reference')->unique();
            $table->string('provider_reference')->nullable()->index();
            $table->string('gateway')->default('flutterwave');
            $table->decimal('amount', 15, 4)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->string('status')->default('pending')->index();
            $table->string('payment_method')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('currency', 3)->default('USD');
            $table->decimal('balance', 15, 4)->default(0);
            $table->decimal('available_balance', 15, 4)->default(0);
            $table->decimal('pending_balance', 15, 4)->default(0);
            $table->timestamps();

            $table->unique(['tenant_id', 'currency']);
        });

        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('bank_code');
            $table->string('bank_name');
            $table->string('account_number');
            $table->string('account_name');
            $table->string('verification_status')->default('pending')->index();
            $table->timestamp('verified_at')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bank_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
            $table->string('reference')->unique();
            $table->decimal('amount', 15, 4)->default(0);
            $table->decimal('fee', 15, 4)->default(0);
            $table->decimal('net_amount', 15, 4)->default(0);
            $table->string('account_number');
            $table->string('account_name');
            $table->string('status')->default('pending')->index();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->string('provider_reference')->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->string('type')->index();
            $table->decimal('amount', 15, 4)->default(0);
            $table->decimal('balance_before', 15, 4)->default(0);
            $table->decimal('balance_after', 15, 4)->default(0);
            $table->string('direction', 10)->default('credit');
            $table->string('status')->default('completed')->index();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('withdrawal_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('subject');
            $table->string('category')->default('general');
            $table->string('priority')->default('medium');
            $table->string('status')->default('open')->index();
            $table->timestamps();
        });

        Schema::create('support_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('support_tickets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('message');
            $table->string('attachment')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->string('entity_type')->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('content_sections', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('active')->index();
            $table->timestamps();
        });

        Schema::create('content_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('content_sections')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('icon')->nullable();
            $table->string('link')->nullable();
            $table->integer('sort_order')->default(0);
            $table->string('status')->default('active')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_items');
        Schema::dropIfExists('content_sections');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('support_messages');
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('withdrawals');
        Schema::dropIfExists('bank_accounts');
        Schema::dropIfExists('wallets');
        Schema::dropIfExists('payments');
    }
};
