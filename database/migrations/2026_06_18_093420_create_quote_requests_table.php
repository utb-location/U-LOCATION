<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('quote_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('name');
            $table->string('organization')->nullable();
            $table->string('phone');
            $table->string('email')->nullable();
            $table->date('departure_date');
            $table->date('return_date')->nullable();
            $table->string('origin');
            $table->string('destination');
            $table->unsignedSmallInteger('passengers');
            $table->string('service_type');
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('driver_required')->default(true);
            $table->text('message')->nullable();
            $table->string('status')->default('Nouvelle demande');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('quote_requests'); }
};