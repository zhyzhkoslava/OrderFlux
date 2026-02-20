<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table): void {
            $table->id();
            $table->string('public_id')->unique();
            $table->foreignId('restaurant_id')->constrained('restaurants')->restrictOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone');
            $table->timestampsTz();

            $table->index(['restaurant_id', 'phone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
