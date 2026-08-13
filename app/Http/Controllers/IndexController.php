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
     * API fallback 入口，记录未命中的请求路径。
     */
    public function fallback(Request $request): ApiResponse
    {
        return $this->error(404, 'Page Not Found')->withStatusCode(404);
    }

    /**
     * API 首页
     */
    public function index(Request $request): ApiResponse
    {
        return $this->success();
    }
}
