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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('payment_type', ['tunai', 'non_tunai'])->default('non_tunai');
            $table->string('external_id')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('status')->nullable();
            $table->string('amount')->nullable();
            $table->string('adjusted_received_amount')->nullable();
            $table->string('fees_paid_amount')->nullable();
            $table->string('payment_channel')->nullable();
            $table->string('payment_destination')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->unsignedBigInteger('history_id');
            $table->timestamps();

            $table->foreign('history_id')->references('id')->on('histories')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['history_id']);
        });
        Schema::dropIfExists('transactions');
    }
};
