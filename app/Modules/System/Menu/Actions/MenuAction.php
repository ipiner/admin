<?php

declare(strict_types=1);

namespace App\Modules\System\Menu\Actions;

use App\Models\System\Menu;
use App\Modules\System\Menu\MenuService;
use Illuminate\Support\Str;
use Pin\Faker\Fake;
use Pin\Tree\Actions\Action;
use Pin\Validation\Rules\Unique;

/**
 * 菜单创建和更新动作共享的验证规则。
 */
class MenuAction extends Action
{
    /**
     * 注入菜单树写入服务。
     */
    public function __construct(MenuService $service)
    {
        parent::__construct($service);
    }

    /**
     * 补充菜单测试数据的默认图标字段。
     */
    public static function fake(array $attributes = []): array
    {
        return parent::fake(['icon' => '']);
    }

    /**
     * 菜单创建和更新共享的基础验证规则。
     */
    protected function basicRules(?int $id = null, ?int $pid = null): array
    {
        $id ??= (int) $this->context->get('id');

        return [
            ...parent::basicRules($id, $pid),
            // 菜单编码
            'code' => [
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
            'visible' => 'integer|in:0,1',

            // 菜单图标
            'icon' => 'nullable|string|max:45',

            // 前端路由，以 `/` 开头
            'route' => [
                'required_if:type,'.Menu::MENU,
                'nullable',
                'max:45',
                'starts_with:/',
                new Unique(Menu::class)->ignore($id),
                Fake::make(fn () => '/'.Str::random()),
            ],

            /**
             * 启用
             *
             * @example 1
             */
            'enabled' => 'integer|in:0,1,2|fake:in,0,1',
        ];
    }
}
