<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_addresses', function (Blueprint $table): void {
            $table->id();
            $table->string('public_id')->unique();
            $table->foreignId('restaurant_id')->constrained('restaurants')->restrictOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('city');
            $table->string('street');
            $table->string('building');
            $table->string('apartment')->nullable();
            $table->string('comment')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_addresses');
    }
};
