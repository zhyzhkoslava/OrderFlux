<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_item_modifiers', function (Blueprint $table): void {
            $table->id();
            $table->string('public_id')->unique();
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $table->string('snapshot_name');
            $table->enum('type', ['add', 'remove']);
            $table->unsignedInteger('quantity')->default(1);
            $table->integer('price_cents')->default(0);
            $table->integer('line_total_cents')->default(0);
            $table->char('currency', 3);
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_item_modifiers');
    }
};
