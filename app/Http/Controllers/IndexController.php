<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Dedoc\Scramble\Attributes\ExcludeAllRoutesFromDocs;
use Illuminate\Http\Request;
use Pin\Http\ApiResponse;

/**
 * @codeCoverageIgnore
 */
#[ExcludeAllRoutesFromDocs]
class IndexController extends Controller
{
    /**
     * 预热 CSRF Cookie 的空响应接口。
     */
    public function csrf(): ApiResponse
    {
        return $this->success();
    }

    /**
     * API fallback 入口，记录未命中的请求路径。
     */
    public function fallback(Request $request): ApiResponse
    {
        return $this->error(404, 'Page Not Found')->withStatusCode(404);
    }

    /**
     * 健康检查接口，可按需返回请求头用于调试。
     */
    public function index(Request $request): ApiResponse
    {
        $data = $request->query('headers') === 'with-headers'
            ? ['headers' => $request->header()]
            : [];

        return $this->success($data);
    }
}
