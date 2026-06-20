<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_code', 100)->unique();
            $table->enum('type', ['goods_in', 'goods_out', 'mutation']);
            $table->foreignId('item_id')->constrained('items')->restrictOnDelete();
            $table->integer('qty');
            $table->string('batch_no', 100)->nullable();
            $table->date('expired_at')->nullable();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('origin_location_id')->nullable()->constrained('locations')->restrictOnDelete();
            $table->foreignId('destination_location_id')->nullable()->constrained('locations')->restrictOnDelete();
            $table->timestamp('transaction_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
