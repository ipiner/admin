<?php

declare(strict_types=1);

namespace App\Modules\System\Role;

use App\Models\System\Role;
use Pin\Services\ModelService;

/**
 * @extends ModelService<Role>
 */
class RoleService extends ModelService
{
    /**
     * @param  Role  $model
     */
    protected function updating($model, array &$data): void
    {
        RoleGuard::ensureUpdatable($model);
        parent::updating($model, $data);
    }

    /**
     * @param  Role  $model
     */
    protected function deleting($model): void
    {
        RoleGuard::ensureDeletable($model);
        parent::deleting($model);
    }
}
