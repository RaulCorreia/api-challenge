<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id_from');
            $table->unsignedBigInteger('user_id_to');
            $table->decimal('amount', 15, 2);
            $table->datetime('transaction_date');
            $table->tinyInteger('transaction_type');
            $table->timestamps();

            $table->foreign('user_id_from')->references('id')->on('users');
            $table->foreign('user_id_to')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
