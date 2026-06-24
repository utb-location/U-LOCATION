<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {Schema::table('quote_requests',function(Blueprint $table){$table->boolean('whatsapp_consent')->default(false)->after('email_consent_at');$table->timestamp('whatsapp_consent_at')->nullable()->after('whatsapp_consent');});Schema::table('sms_campaigns',function(Blueprint $table){$table->unsignedInteger('whatsapp_sent_count')->default(0)->after('email_failed_count');});Schema::table('sms_messages',function(Blueprint $table){$table->string('whatsapp_status')->default('Non requis')->after('email_status');$table->timestamp('whatsapp_sent_at')->nullable()->after('email_sent_at');});}
 public function down(): void {Schema::table('sms_messages',fn(Blueprint $table)=>$table->dropColumn(['whatsapp_status','whatsapp_sent_at']));Schema::table('sms_campaigns',fn(Blueprint $table)=>$table->dropColumn('whatsapp_sent_count'));Schema::table('quote_requests',fn(Blueprint $table)=>$table->dropColumn(['whatsapp_consent','whatsapp_consent_at']));}
};
