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
        $this->schema()->create('articles', function (Blueprint $table) {
            $this->useTable($table);
            $this->id();
            $this->unsignedInteger('category_id', '分类id|article_categories.id')->default(0)->index();
            $this->string('title', '标题');
            $table->text('content')->comment('内容');
            $this->version();
            $this->blameable();
            $this->timestamps();
            $this->deleted();
            $table->unique(['title', 'category_id', 'deleted_at']);
            $table->comment($this->makeComment('文章表', 'pin'));
        });

        $this->schema()->create('article_categories', function (Blueprint $table) {
            $this->useTable($table);
            $this->id(false);
            $this->unsignedInteger('pid', '父id|article_categories.id')->default(0)->index();
            $this->string('name', '分类名称', 30);
            $this->string('path', '分类路径|...父父id,父id,id', 100)->unique();
            $this->unsignedTinyInteger('level', '层级')->default(1);
            $this->unsignedInteger('sort', '排序');
            $this->version();
            $this->blameable();
            $this->timestamps();
            $this->deleted();

            $table->unique(['name', 'pid', 'deleted_at']);
            $table->index(['level', 'sort']);
            $table->comment($this->makeComment('文章分类表', 'pin'));
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->schema()->dropIfExists('articles');
        $this->schema()->dropIfExists('article_categories');
    }
};
