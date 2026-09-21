<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\System\Menu;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Pin\Support\Facades\Tree;
use Pin\Tree\TreeModel;

/**
 * 树节点路径检查
 */
#[Signature('app:check-tree-paths {--table=menus : 树形表名}')]
#[Description('检查树节点路径')]
class CheckTreePaths extends Command
{
    /**
     * @var array<string, class-string<TreeModel>> 表与模型映射
     */
    protected const array TABLES = [
        'menus' => Menu::class,
    ];

    /**
     * 校验并输出异常路径
     */
    public function handle(): int
    {
        $table = $this->option('table');
        $modelClass = static::TABLES[$table] ?? null;

        if (! $modelClass) {
            $this->error('Unsupported table: '.$table);

            return self::INVALID;
        }

        $errors = Tree::check($modelClass::query()->get(['id', 'pid', 'path', 'level']));

        if (! $errors) {
            $this->info('All paths are valid');

            return self::SUCCESS;
        }

        foreach ($errors as $error) {
            $this->error(implode(' ', $error));
        }

        return self::FAILURE;
    }
}
