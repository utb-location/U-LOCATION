<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {Schema::table('users',function(Blueprint $table){$table->boolean('active')->default(true)->after('role');$table->boolean('must_change_password')->default(false)->after('active');$table->timestamp('last_login_at')->nullable()->after('must_change_password');});DB::table('users')->where('role','admin')->update(['role'=>'super_admin']);}
 public function down(): void {Schema::table('users',function(Blueprint $table){$table->dropColumn(['active','must_change_password','last_login_at']);});DB::table('users')->where('role','super_admin')->update(['role'=>'admin']);}
};
