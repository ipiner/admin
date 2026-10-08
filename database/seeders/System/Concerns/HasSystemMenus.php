<?php

declare(strict_types=1);

namespace Database\Seeders\System\Concerns;

use App\Routes\System\AdminRoute;
use App\Routes\System\LogRoute;
use App\Routes\System\MenuRoute;
use App\Routes\System\RoleRoute;

trait HasSystemMenus
{
    protected function admin(): array
    {
        return [
            'name' => '管理员',
            'icon' => 'ant-design:user-outlined',
            'url' => AdminRoute::Index,
        ];
    }

    protected function log(): array
    {
        return [
            'name' => '日志',
            'code' => 'system.log',
            'icon' => 'ant-design:security-scan-filled',
            'url' => '/system/log',
            'children' => [
                [
                    'icon' => 'ant-design:login-outlined',
                    'url' => \Pin\Modules\Log\LogRoute::LoginLog,
                ],
                [
                    'icon' => 'ant-design:console-sql-outlined',
                    'url' => \Pin\Modules\Log\LogRoute::OperationLog,
                ],
                [
                    'icon' => 'ant-design:upload-outlined',
                    'url' => LogRoute::UploadLog,
                ],
                [
                    'icon' => 'ant-design:audit-outlined',
                    'url' => \Pin\Modules\Log\LogRoute::ActivityLog,
                ],
            ],
        ];
    }

    protected function menu(): array
    {
        return [
            'name' => '菜单',
            'enabled' => 2,
            'icon' => 'ant-design:unordered-list-outlined',
            'url' => MenuRoute::Index,
        ];
    }

    protected function role(): array
    {
        return [
            'name' => '角色',
            'icon' => 'ant-design:team-outlined',
            'url' => RoleRoute::Index,
        ];
    }

    protected function system(): array
    {
        return [
            'name' => '系统',
            'code' => 'system',
            'icon' => 'ant-design:setting-outlined',
            'url' => '/system',
            'children' => [
                $this->role(),
                $this->admin(),
                $this->menu(),
                $this->log(),
            ],
        ];
    }
}
