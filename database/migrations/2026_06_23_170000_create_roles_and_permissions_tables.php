<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('protected')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('permission_role', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->primary(['role_id', 'permission_id']);
        });

        $permissions = config('roles.permission_catalog', []);
        foreach ($permissions as $slug => $permission) {
            DB::table('permissions')->insert([
                'slug' => $slug,
                'name' => $permission['name'],
                'description' => $permission['description'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach (config('roles.labels', []) as $slug => $name) {
            DB::table('roles')->insert([
                'slug' => $slug,
                'name' => $name,
                'description' => $slug === 'super_admin' ? 'Acces complet a la plateforme.' : null,
                'protected' => $slug === 'super_admin',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $permissionIds = DB::table('permissions')->pluck('id', 'slug');
        $roleIds = DB::table('roles')->pluck('id', 'slug');
        foreach (config('roles.permissions', []) as $permissionSlug => $roleSlugs) {
            foreach ($roleSlugs as $roleSlug) {
                if (isset($permissionIds[$permissionSlug], $roleIds[$roleSlug])) {
                    DB::table('permission_role')->insert([
                        'role_id' => $roleIds[$roleSlug],
                        'permission_id' => $permissionIds[$permissionSlug],
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions');
    }
};
