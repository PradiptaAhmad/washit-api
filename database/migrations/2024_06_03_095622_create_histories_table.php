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
            $table->unsignedBigInteger('id')->unique()->primary();
            $table->string("no_pemesanan")->unique();
            $table->enum('jenis_pemesanan', ['antar_jemput', 'antar_mandiri'])->default('antar_jemput');
            $table->string("nama_pemesan");
            $table->string("nomor_telepon");
            $table->string("alamat")->nullable();
            $table->string("metode_pembayaran")->default("tunai");
            $table->integer("berat_laundry")->nullable();
            $table->integer("total_harga")->nullable();
            $table->text('catatan')->nullable();
            $table->enum('status', ['process', 'completed', 'canceled'])->default('process');
            $table->timestamp("tanggal_pengambilan")->nullable();
            $table->timestamp('tanggal_estimasi')->nullable();
            $table->string('laundry_service')->nullable();
            $table->string('laundry_description')->nullable();
            $table->integer('laundry_price')->nullable();
            $table->unsignedBigInteger("user_id")->default(0);
            $table->timestamps();

            $table->foreign("user_id")->references("id")->on("users")->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('histories', function (Blueprint $table) {
            $table->dropForeign(["user_id"]);
        });
        Schema::dropIfExists('histories');
    }
};
