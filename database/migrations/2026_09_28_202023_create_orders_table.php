<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('users');
            $table->foreignId('publisher_id')->constrained('users');
            $table->foreignId('site_id')->constrained();
            $table->string('target_url');
            $table->string('anchor_text');
            $table->text('content')->nullable();
            $table->text('brief')->nullable();
            $table->unsignedInteger('publisher_price');
            $table->unsignedInteger('buyer_price');
            $table->string('status')->default('pending')->index();
            $table->timestamp('deadline_at')->nullable();
            $table->string('published_url')->nullable();
            $table->string('refusal_reason')->nullable();
            $table->text('dispute_reason')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('link_status')->nullable();
            $table->string('link_rel')->nullable();
            $table->string('link_check_detail')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamp('monitor_until')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
