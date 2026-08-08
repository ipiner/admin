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
        $this->schema()->create('uploads', function (Blueprint $table) {
            $this->useTable($table);
            $this->id();
            $this->string('file_id', '文件uuid', 36);
            $this->unsignedBigInteger('uid', '用户id|users.id')->index();
            $this->string('username', '用户名', 30);
            $this->string('user_type', '用户类型|user：用户；admin：管理员；console：控制台用户', 30);
            $this->string('disk', '上传磁盘', 30);
            $this->string('path', '路径|相对配置的disk路径');
            $this->string('url', 'url地址');
            $this->string('name', '显示名');
            $this->string('original_name', '原始名');
            $this->unsignedInteger('size', '大小');
            $this->string('extension', '后缀', 10);
            $this->string('mime_type', 'mime类型', 30);
            $this->unsignedInteger('width', '图片宽')->default(0);
            $this->unsignedInteger('height', '图片高')->default(0);
            $this->string('ip', '用户ip', 45);
            $this->json('info', '其它信息');
            $this->timestamps();
            $this->deleted();

            $table->comment($this->makeComment('上传文件表', 'pin'));
        });

        $this->schema()->create('upload_logs', function (Blueprint $table) {
            $this->useTable($table);
            $this->id();
            $this->string('file_id', '文件uuid', 36);
            $this->unsignedBigInteger('uid', '用户id|users.id')->index();
            $this->string('username', '用户名', 30);
            $this->string('user_type', '用户类型|user：用户；admin：管理员；console：控制台用户', 30);
            $this->string('path', '路径|相对配置的disk路径');
            $this->string('url', 'url地址')->default('');
            $this->string('name', '显示名');
            $this->string('original_name', '原始名');
            $this->unsignedInteger('size', '大小');
            $this->string('extension', '后缀', 10);
            $this->string('mime_type', 'mime类型', 30);
            $this->string('disk', '上传磁盘', 30);
            $this->unsignedInteger('width', '图片宽')->default(0);
            $this->unsignedInteger('height', '图片高')->default(0);
            $this->unsignedInteger('code', '返回码')->default(0);
            $this->string('message', '返回信息', null, true);
            $this->string('ip', '用户ip', 45);
            $this->json('info', '其它信息');
            $this->timestamps();

            $table->comment($this->makeComment('文件上传日志表', 'pin'));
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->schema()->dropIfExists('uploads');
        $this->schema()->dropIfExists('upload_logs');
    }
};
