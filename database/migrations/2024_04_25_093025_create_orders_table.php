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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string("no_pemesanan")->unique();
            $table->enum('jenis_pemesanan', ['antar_jemput', 'antar_mandiri'])->default('antar_jemput');
            $table->string("nama_pemesan");
            $table->string("nomor_telepon");
            $table->string("alamat")->nullable();
            $table->integer("berat_laundry")->nullable();
            $table->integer("total_harga")->nullable();
            $table->timestamp("tanggal_pemesanan")->nullable();
            $table->datetime("tanggal_pengambilan")->nullable();
            $table->unsignedBigInteger("laundry_id")->default(0);
            $table->unsignedBigInteger("user_id")->default(0);
            $table->timestamps();

            $table->foreign("laundry_id")->references("id")->on("laundries")->onDelete("cascade");
            $table->foreign("user_id")->references("id")->on("users")->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(["laundry_id"]);
            $table->dropForeign(["user_id"]);
        });
        Schema::dropIfExists('orders');
    }
};
