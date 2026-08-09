<?php

declare(strict_types=1);

namespace App\Modules\System\Menu\Actions;

use App\Models\System\Menu;
use App\Routes\AccountRoute;
use App\Routes\Auth\LoginRoute;
use App\Routes\System\MenuRoute;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Pin\Access\Attributes\Access;
use Pin\Action\Action;
use Pin\Captcha\CaptchaRoute;
use Pin\Route\RouteRegistry;
use Pin\Route\RouteRegistryItem;

/**
 * 从已注册路由中提取还可以创建为菜单权限的编码。
 */
class AvailableCodesAction extends Action
{
    /**
     * 返回可用菜单编码列表。
     */
    public function handle(): Collection
    {
        $all = (bool) ($this->validated()['all'] ?? false);
        $names = Menu::findAll()->keyBy('code');
        $items = RouteRegistry::items()->filter(function (RouteRegistryItem $item) use ($names, $all) {
            return ($all || ! isset($names[$item->route->getName()]))
                && $this->shouldExclude($item);
        });

        return $items->sortKeys()
            ->map(function (RouteRegistryItem $item) {
                $routeName = $item->route->getName();

                return [
                    'label' => $names[$routeName]?->name ?? $item->case->title(),
                    'value' => $routeName,
                    'name' => Str::of($routeName)
                        ->replace(['.', '-'], '_')
                        ->upper(),
                ];
            })
            ->values();
    }

    /**
     * 过滤不应作为菜单权限创建的路由。
     */
    protected function shouldExclude(RouteRegistryItem $item): bool
    {
        if (
            in_array(get_class($item->case), [AccountRoute::class, LoginRoute::class, CaptchaRoute::class], true)
            || in_array($item->case, [MenuRoute::AvailableCodes])
            || Str::endsWith($item->case->name(), ['.selector'])
            || ! $item->case->title()
        ) {
            return false;
        }

        $attr = $item->case->attribute(Access::class);
        if ($attr !== null && $attr->value !== $item->case->name()) {
            return false;
        }

        return true;
    }

    /**
     * 可用编码查询参数验证规则。
     */
    protected function rules(): array
    {
        return [
            /**
             * 是否返回所有可用的菜单编码
             */
            'all' => 'nullable|in:0,1',
        ];
    }
}
