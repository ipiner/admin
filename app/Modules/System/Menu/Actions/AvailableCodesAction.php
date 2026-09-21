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
 * 可用菜单编码
 */
class AvailableCodesAction extends Action
{
    /**
     * 获取可用编码
     *
     * @return Collection<int, array{label: string, value: string, name: string}>
     */
    public function handle(): Collection
    {
        $all = (bool) ($this->validated()['all'] ?? false);
        $names = Menu::findAll()->pluck('name', 'code');
        $items = RouteRegistry::items()->filter(
            fn (RouteRegistryItem $item): bool => ($all || ! $names->has($item->route->getName()))
                && $this->canUseAsMenu($item)
        );

        return $items->sortKeys()
            ->map(static function (RouteRegistryItem $item) use ($names): array {
                $routeName = $item->route->getName();

                return [
                    'label' => $names->get($routeName) ?? $item->case->title(),
                    'value' => $routeName,
                    'name' => Str::upper(str_replace(['.', '-'], '_', $routeName)),
                ];
            })
            ->values();
    }

    /**
     * 是否可作为菜单权限
     */
    protected function canUseAsMenu(RouteRegistryItem $item): bool
    {
        if (
            in_array($item->case::class, [
                AccountRoute::class, LoginRoute::class, CaptchaRoute::class,
            ], true)
            || $item->case === MenuRoute::AvailableCodes
            || Str::endsWith($item->route->getName(), '.selector')
            || ! $item->case->title()
        ) {
            return false;
        }

        $access = $item->case->attribute(Access::class);

        return ! $access || $access->value === $item->route->getName();
    }

    /**
     * 查询验证规则
     */
    public function rules(): array
    {
        return [
            /**
             * 是否返回所有可用的菜单编码
             */
            'all' => 'nullable|in:0,1',
        ];
    }
}
