<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('customer_feedback', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('quote_request_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('type');
            $table->string('category');
            $table->unsignedTinyInteger('rating');
            $table->text('message');
            $table->string('status')->default('Nouveau');
            $table->string('priority')->default('Normale');
            $table->text('admin_response')->nullable();
            $table->timestamp('handled_at')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['status', 'type']);
        });
    }

    public function down(): void { Schema::dropIfExists('customer_feedback'); }
};
