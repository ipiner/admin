<?php

declare(strict_types=1);

namespace App\Modules\System\Admin;

use App\Models\System\Admin;
use Override;
use Pin\Services\ModelService;

/**
 * 管理员服务
 *
 * @extends ModelService<Admin>
 */
class AdminService extends ModelService
{
    /**
     * @param  Admin  $model
     */
    #[Override]
    protected function updating($model, array &$data): void
    {
        AdminGuard::ensureUpdatable($model);
        parent::updating($model, $data);
    }

    /**
     * @param  Admin  $model
     */
    #[Override]
    protected function deleting($model): void
    {
        AdminGuard::ensureDeletable($model);
        parent::deleting($model);
    }
}
