<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\System\Admin;

/**
 * 登录失败事件。
 */
class LoginFailed
{
    /**
     * @param  int  $code  对外错误码
     * @param  int  $internalCode  日志错误码
     * @param  array<string, mixed>  $context  日志上下文
     */
    public function __construct(
        public Admin $admin,
        public int $code,
        public string $message,
        public int $internalCode,
        public array $context = []
    ) {
    }
}
