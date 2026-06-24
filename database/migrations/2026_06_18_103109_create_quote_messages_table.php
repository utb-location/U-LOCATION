<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('quote_messages',function(Blueprint $table){$table->id();$table->foreignId('quote_request_id')->constrained()->cascadeOnDelete();$table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();$table->string('recipient');$table->string('subject');$table->longText('body');$table->timestamp('sent_at')->nullable();$table->timestamps();}); }
 public function down(): void { Schema::dropIfExists('quote_messages'); }
};