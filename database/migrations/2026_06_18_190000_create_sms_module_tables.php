<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::table('quote_requests',function(Blueprint $table){$table->boolean('sms_consent')->default(false)->after('phone');$table->timestamp('sms_consent_at')->nullable()->after('sms_consent');});
  Schema::create('sms_campaigns',function(Blueprint $table){$table->id();$table->string('name');$table->string('sender',11)->default('UTBLOCATION');$table->text('message');$table->string('audience');$table->string('status')->default('Brouillon');$table->timestamp('scheduled_at')->nullable();$table->timestamp('sent_at')->nullable();$table->unsignedInteger('recipient_count')->default(0);$table->unsignedInteger('sent_count')->default(0);$table->unsignedInteger('failed_count')->default(0);$table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();$table->timestamps();});
  Schema::create('sms_messages',function(Blueprint $table){$table->id();$table->foreignId('sms_campaign_id')->nullable()->constrained()->cascadeOnDelete();$table->foreignId('quote_request_id')->nullable()->constrained()->nullOnDelete();$table->string('recipient_name');$table->string('phone',24);$table->text('message');$table->boolean('is_test')->default(false);$table->string('status')->default('En attente');$table->string('provider')->nullable();$table->string('provider_message_id')->nullable();$table->text('error_message')->nullable();$table->json('provider_response')->nullable();$table->timestamp('sent_at')->nullable();$table->timestamps();$table->index(['status','phone']);});
 }
 public function down(): void {Schema::dropIfExists('sms_messages');Schema::dropIfExists('sms_campaigns');Schema::table('quote_requests',function(Blueprint $table){$table->dropColumn(['sms_consent','sms_consent_at']);});}
};
