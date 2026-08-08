<?php

declare(strict_types=1);

namespace App\Modules\System\Menu;

use App\Models\System\Menu;
use Pin\Tree\ModelService;

/**
 * @extends ModelService<Menu>
 */
class MenuService extends ModelService
{
    public string $resourceName = '菜单';

    /**
     * @param  Menu  $model
     */
    protected function deleting($model): void
    {
        MenuGuard::ensureDeletable($model);
        parent::deleting($model);
    }

    /**
     * @param  Menu  $model
     */
    protected function updating($model, array &$data): void
    {
        MenuGuard::ensureEnabledStatusChangeAllowed($model, $data['enabled'] ?? null);
        parent::updating($model, $data);
    }
}
