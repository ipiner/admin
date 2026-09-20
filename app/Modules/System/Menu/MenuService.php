<?php

declare(strict_types=1);

namespace App\Modules\System\Menu;

use App\Models\System\Menu;
use Override;
use Pin\Tree\ModelService;

/**
 * 菜单服务。
 *
 * @extends ModelService<Menu>
 */
class MenuService extends ModelService
{
    public string $resourceName = '菜单';

    /**
     * @param  Menu  $model
     */
    #[Override]
    protected function deleting($model): void
    {
        MenuGuard::ensureDeletable($model);
        parent::deleting($model);
    }

    /**
     * @param  Menu  $model
     */
    #[Override]
    protected function updating($model, array &$data): void
    {
        MenuGuard::ensureEnabledStatusChangeAllowed(
            $model,
            isset($data['enabled']) ? (int) $data['enabled'] : null
        );
        parent::updating($model, $data);
    }
}
