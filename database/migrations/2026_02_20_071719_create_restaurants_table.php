<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurants', function (Blueprint $table): void {
            $table->id();
            $table->string('public_id')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('address');
            $table->string('timezone');
            $table->char('currency', 3);
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurants');
    }
};
