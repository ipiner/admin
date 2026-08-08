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
        $this->schema()->create('admins', function (Blueprint $table) {
            $this->useTable($table);
            $this->id();
            $this->string('username', '用户名', 30);
            $this->string('realname', '姓名', 30);
            $this->string('password', '密码');
            $this->string('salt', '加密盐值', 8);
            $this->string('avatar', '头像', null, true);
            $this->string('captcha_rule', '验证码规则', 30, true);
            $this->unsignedSmallInteger('login_num', '登录次数')->default(0);
            $this->timestamp('last_login_at', '最后登录时间');
            $this->string('last_login_ip', '最后登录ip', 15, true);
            $this->version();
            $this->timestamps();
            $this->deleted();

            $table->unique(['username', 'deleted_at']);
            $table->comment($this->makeComment('管理员表', 'pin'));
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->schema()->dropIfExists('admins');
    }
};
