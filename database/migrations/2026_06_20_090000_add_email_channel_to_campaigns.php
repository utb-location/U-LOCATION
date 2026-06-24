<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::table('quote_requests',function(Blueprint $table){$table->boolean('email_consent')->default(false)->after('sms_consent_at');$table->timestamp('email_consent_at')->nullable()->after('email_consent');});
  Schema::table('sms_campaigns',function(Blueprint $table){$table->string('channel')->default('sms')->after('sender');$table->string('email_subject')->nullable()->after('message');$table->unsignedInteger('email_sent_count')->default(0)->after('failed_count');$table->unsignedInteger('email_failed_count')->default(0)->after('email_sent_count');});
  Schema::table('sms_messages',function(Blueprint $table){$table->string('email')->nullable()->after('phone');$table->string('email_status')->default('Non requis')->after('status');$table->text('email_error')->nullable()->after('error_message');$table->timestamp('email_sent_at')->nullable()->after('sent_at');});
 }
 public function down(): void {Schema::table('sms_messages',function(Blueprint $table){$table->dropColumn(['email','email_status','email_error','email_sent_at']);});Schema::table('sms_campaigns',function(Blueprint $table){$table->dropColumn(['channel','email_subject','email_sent_count','email_failed_count']);});Schema::table('quote_requests',function(Blueprint $table){$table->dropColumn(['email_consent','email_consent_at']);});}
};
