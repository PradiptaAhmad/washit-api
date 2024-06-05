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
        Schema::create('histories', function (Blueprint $table) {
            $table->id();
            $table->string("no_pemesanan")->unique();
            $table->enum('jenis_pemesanan', ['antar_jemput', 'antar_mandiri'])->default('antar_jemput');
            $table->string("nama_pemesan");
            $table->string("nomor_telepon");
            $table->string("alamat")->nullable();
            $table->integer("berat_laundry")->nullable();
            $table->integer("total_harga")->nullable();
            $table->string('payment_method')->default('cashless');
            $table->timestamp("tanggal_pemesanan")->nullable();
            $table->datetime("tanggal_pengambilan")->nullable();
            $table->unsignedBigInteger("laundry_id")->nullable();
            $table->unsignedBigInteger("user_id")->nullable();
            $table->unsignedBigInteger("trasanction_id");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('histories');
    }
};
