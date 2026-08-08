<?php

declare(strict_types=1);

namespace App\Modules\System\Log;

use App\Models\UploadLog;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Pin\Errors\Errors;
use Pin\Http\ApiResponse;
use Pin\Modules\Log\Controllers\Controller;
use Pin\Pagination\Pagination;
use Pin\Validation\QueryableRules as Queryable;

/**
 * 查询上传日志和上传结果筛选项。
 */
#[Group('系统 / 日志')]
class UploadLogController extends Controller
{
    /**
     * 上传日志
     *
     * @return ApiResponse<Pagination<UploadLog[]>>
     */
    public function index(Request $request): ApiResponse
    {
        $rules = [
            ...$this->service->baseRules(),
            // 文件名
            'name' => Queryable::like(),

            // 原始文件名
            'original_name' => Queryable::like(),

            // 文件路径
            'path' => Queryable::like(),

            // 文件后缀
            'extension' => Queryable::in(),

            // 上传返回码
            'code' => Queryable::inNumeric(),
        ];
        $request->validate($rules);

        return $this->success($this->service->pagination($rules));
    }

    /**
     * 上传日志筛选项
     *
     * @response ApiResponse<array{
     *     extensions: Pin\Scramble\SelectOption[],
     *     codes: Pin\Scramble\SelectOption[]
     * }>
     */
    public function options(): ApiResponse
    {
        $data = $this->service->options(['extension', 'code'], fn (Collection $data) => [
            'extensions' => $data->keyBy('extension')
                ->keys()
                ->sort()
                ->values()
                ->map(fn ($item) => ['label' => $item, 'value' => $item])
                ->toArray(),
            'codes' => $data->keyBy('code')
                ->keys()
                ->sort()
                ->values()
                ->map(fn ($item) => [
                    'label' => $item.'/'.($item === 0 ? '上传成功' : Errors::get($item)->message()),
                    'value' => $item,
                ])
                ->toArray(),
        ]);

        return $this->success($data);
    }

    /**
     * 日志模型
     */
    protected function modelClass(): string
    {
        return UploadLog::class;
    }
}
