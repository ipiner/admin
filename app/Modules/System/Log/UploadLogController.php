<?php

declare(strict_types=1);

namespace App\Modules\System\Log;

use App\Models\UploadLog;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Override;
use Pin\Errors\Errors;
use Pin\Http\ApiResponse;
use Pin\Modules\Log\Controllers\Controller;
use Pin\Pagination\Pagination;
use Pin\Scramble\SelectOption;
use Pin\Validation\QueryableRules;

/**
 * 上传日志。
 */
#[Group('系统 / 日志')]
class UploadLogController extends Controller
{
    /**
     * 上传日志
     *
     * @return ApiResponse<Pagination<UploadLog>>
     */
    public function index(Request $request): ApiResponse
    {
        $rules = [
            ...Arr::except($this->service->baseRules(), 'request_id'),
            // 文件名
            'name' => QueryableRules::like(),

            // 原始文件名
            'original_name' => QueryableRules::like(),

            // 文件路径
            'path' => QueryableRules::like(),

            // 文件后缀
            'extension' => QueryableRules::in(),
            'extension.*' => 'string',

            // 上传返回码
            'code' => QueryableRules::inNumeric(),
            'code.*' => 'integer',
        ];
        $request->validate($rules);

        return $this->success($this->service->pagination($rules));
    }

    /**
     * 上传日志筛选项
     *
     * @return ApiResponse<array{
     *     extensions: SelectOption[],
     *     codes: SelectOption[]
     * }>
     */
    public function options(): ApiResponse
    {
        $data = $this->service->options(['extension', 'code'], fn (Collection $logs): array => [
            'extensions' => $this->extensionOptions($logs),
            'codes' => $this->codeOptions($logs),
        ]);

        return $this->success($data);
    }

    /**
     * 返回码筛选项。
     *
     * @param  Collection<int, UploadLog>  $logs
     * @return list<array{label: string, value: int}>
     */
    protected function codeOptions(Collection $logs): array
    {
        return $logs->pluck('code', 'code')
            ->sort(SORT_NUMERIC)
            ->values()
            ->map(static fn (int $code): array => [
                'label' => $code.'/'.($code === 0 ? '上传成功' : Errors::getMessage($code)),
                'value' => $code,
            ])
            ->all();
    }

    /**
     * 后缀筛选项。
     *
     * @param  Collection<int, UploadLog>  $logs
     * @return list<array{label: string, value: string}>
     */
    protected function extensionOptions(Collection $logs): array
    {
        return $logs->pluck('extension', 'extension')
            ->sort(SORT_STRING)
            ->values()
            ->map(static fn (string $extension): array => [
                'label' => $extension,
                'value' => $extension,
            ])
            ->all();
    }

    /**
     * 日志模型。
     *
     * @return class-string<UploadLog>
     */
    #[Override]
    protected function modelClass(): string
    {
        return UploadLog::class;
    }
}
