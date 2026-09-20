<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Dedoc\Scramble\Attributes\ExcludeAllRoutesFromDocs;
use Pin\Http\ApiResponse;

/**
 * API 公共入口。
 *
 * @codeCoverageIgnore
 */
#[ExcludeAllRoutesFromDocs]
class IndexController extends Controller
{
    /**
     * 路由未匹配响应。
     *
     * @return ApiResponse<null>
     */
    public function fallback(): ApiResponse
    {
        return $this->error(404, 'Page Not Found')->withStatusCode(404);
    }

    /**
     * API 首页。
     *
     * @return ApiResponse<null>
     */
    public function index(): ApiResponse
    {
        return $this->success();
    }
}
