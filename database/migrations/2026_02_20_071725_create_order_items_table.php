<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table): void {
            $table->id();
            $table->string('public_id')->unique();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('sku');
            $table->unsignedInteger('qty');
            $table->integer('price_cents');
            $table->char('currency', 3);
            $table->timestamps();

            $table->index(['order_id', 'sku']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
