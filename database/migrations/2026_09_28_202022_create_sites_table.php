<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('url');
            $table->string('domain')->unique();
            $table->string('language', 2);
            $table->string('category');
            $table->text('description')->nullable();
            $table->unsignedInteger('price_dzd');
            $table->string('link_attribute')->default('sponsored');
            $table->unsignedTinyInteger('turnaround_days')->default(5);
            $table->unsignedInteger('monthly_traffic')->nullable();
            $table->unsignedTinyInteger('domain_rating')->nullable();
            $table->string('verification_token', 40);
            $table->timestamp('verified_at')->nullable();
            $table->string('status')->default('pending')->index();
            $table->string('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
