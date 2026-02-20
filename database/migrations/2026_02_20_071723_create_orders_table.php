<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->string('public_id')->unique();
            $table->foreignId('restaurant_id')->constrained('restaurants')->restrictOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->foreignId('delivery_address_id')->nullable()->constrained('delivery_addresses')->nullOnDelete();
            $table->timestampTz('scheduled_at');
            $table->enum('type', ['ASAP', 'PREORDER']);
            $table->string('status');
            $table->integer('total_cents');
            $table->integer('delivery_fee_cents')->default(0);
            $table->char('currency', 3);
            $table->timestampsTz();

            $table->index(['restaurant_id', 'status']);
            $table->index(['restaurant_id', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
