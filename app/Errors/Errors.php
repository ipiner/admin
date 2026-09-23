<?php

declare(strict_types=1);

namespace App\Errors;

use Pin\Errors\Errorful;
use Pin\Errors\IError;

/**
 * 应用错误码（10000 起）
 *
 * - 10000 ~ 19999：通用
 * - 20000 ~ 29999：用户
 */
enum Errors: string implements IError
{
    use Errorful;

    // 登录：21000 ~ 21999

    /**
     * 登录失败
     */
    case LoginFailed = '21000|帐号或密码错误';

    /**
     * 登录帐号不存在
     */
    case LoginAccountNotFound = '21001|帐号或密码错误';

    /**
     * 登录帐号已禁用
     */
    case LoginAccountDisabled = '21002|帐号已禁用';

    /**
     * 登录密码错误
     */
    case LoginPasswordMismatch = '21003|帐号或密码错误';

    /**
     * 禁止登录
     */
    case LoginDisabled = '21004|帐号或密码错误';
}
