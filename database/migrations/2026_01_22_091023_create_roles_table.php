<?php

use Illuminate\Database\Schema\Blueprint;
use Pin\Database\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->schema()->create('roles', function (Blueprint $table) {
            $this->useTable($table);
            $this->id();
            $this->string('name', '角色名称', 30);
            $this->string('remark', '备注', 100, true);
            $this->version();
            $this->timestamps();
            $this->deleted();

            $table->unique(['name', 'deleted_at']);
            $table->comment($this->makeComment('角色表', 'pin'));
        });

        $this->schema()->create('role_admins', function (Blueprint $table) {
            $this->useTable($table);
            $this->id();
            $this->unsignedInteger('role_id', '角色id|roles.id');
            $this->unsignedInteger('uid', '管理员id|admins.id')->index();
            $this->timestamp('created_at', '创建时间')->useCurrent();

            $table->unique(['role_id', 'uid']);
            $table->comment($this->makeComment('角色管理员关系表', 'pin'));
        });

        $this->schema()->create('role_menus', function (Blueprint $table) {
            $this->useTable($table);
            $this->id();
            $this->unsignedInteger('role_id', '角色id|roles.id');
            $this->unsignedInteger('menu_id', '菜单id|menus.id');
            $this->timestamp('created_at', '创建时间')->useCurrent();

            $table->unique(['role_id', 'menu_id']);

            $table->comment($this->makeComment('菜单权限关系表', 'pin'));
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->schema()->dropIfExists('roles');
        $this->schema()->dropIfExists('role_admins');
        $this->schema()->dropIfExists('role_menus');
    }
};
