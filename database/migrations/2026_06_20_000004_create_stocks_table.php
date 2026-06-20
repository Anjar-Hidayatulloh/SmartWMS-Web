<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->restrictOnDelete();
            $table->foreignId('location_id')->constrained('locations')->restrictOnDelete();
            $table->integer('qty')->default(0);
            $table->string('batch_no', 100)->nullable();
            $table->date('expired_at')->nullable();
            $table->enum('status', ['available', 'pending_quarantine', 'quarantined'])->default('available');
            $table->timestamps();

            $table->unique(['item_id', 'location_id', 'batch_no']);
        });

        // Conditionally apply database constraint only for MySQL/PostgreSQL, since SQLite does not support ALTER TABLE ADD CONSTRAINT
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE stocks ADD CONSTRAINT chk_stocks_qty_non_negative CHECK (qty >= 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
