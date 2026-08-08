<?php

namespace App\Console\Commands;

use App\Models\System\Menu;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Pin\Support\Facades\Tree;

/**
 * 检查树形表 path 字段与父子关系是否一致。
 */
#[Signature('app:check-tree-paths --table=')]
#[Description('检查表的path字段是否异常')]
class CheckTreePaths extends Command
{
    /**
     * 可检查的树形表与模型映射。
     */
    private const array TABLES = [
        'menus' => Menu::class,
    ];

    /**
     * 执行菜单树 path 校验并输出异常路径。
     */
    public function handle(): int
    {
        $models = Menu::all();

        $errs = Tree::check($models);

        if (empty($errs)) {
            $this->info('All paths are valid');

            return static::SUCCESS;
        }

        foreach ($errs as $err) {
            $this->error(implode(' ', $err));
        }

        return static::FAILURE;
    }
}
