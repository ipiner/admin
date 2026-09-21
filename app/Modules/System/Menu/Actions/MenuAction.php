<?php

declare(strict_types=1);

namespace App\Modules\System\Menu\Actions;

use App\Models\System\Menu;
use App\Modules\System\Menu\MenuService;
use Illuminate\Support\Str;
use Override;
use Pin\Faker\Fake;
use Pin\Tree\Action;
use Pin\Validation\Rules\Unique;

/**
 * 菜单写入操作
 */
class MenuAction extends Action
{
    public function __construct(MenuService $service)
    {
        parent::__construct($service);
    }

    /**
     * 生成测试数据
     */
    #[Override]
    public static function fake(array $attributes = []): array
    {
        return parent::fake(['icon' => '', ...$attributes]);
    }

    /**
     * 基础验证规则
     */
    #[Override]
    protected function basicRules(?int $id = null, ?int $pid = null): array
    {
        $id ??= (int) $this->context('id');
        $pid ??= (int) $this->payload('pid');

        return [
            ...parent::basicRules($id, $pid),
            // 菜单编码
            'code' => [
                'bail',
                'required',
                'string',
                new Unique(Menu::class)->ignore($id),
            ],

            // 菜单类型
            'type' => 'required|in:'.implode(',', [Menu::MENU, Menu::BUTTON]),

            /**
             * 显示
             *
             * @example 1
             */
            'visible' => $this->visibleRules(),

            // 菜单图标
            'icon' => 'nullable|string|max:45',

            // 前端路由，以 `/` 开头
            'route' => [
                'bail',
                'required_if:type,'.Menu::MENU,
                'nullable',
                'string',
                'max:45',
                'starts_with:/',
                new Unique(Menu::class)->ignore($id),
                Fake::make(static fn () => '/'.Str::random()),
            ],

            /**
             * 启用
             *
             * @example 1
             */
            'enabled' => $this->enabledRules(),
        ];
    }

    /**
     * 启用状态规则
     */
    protected function enabledRules(): string
    {
        return 'integer|in:0,1,2|fake:in,0,1';
    }

    /**
     * 显示状态规则
     */
    protected function visibleRules(): string
    {
        return 'integer|in:0,1';
    }
}
