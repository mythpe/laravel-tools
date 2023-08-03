<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2023 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Traits\BaseController;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Myth\LaravelTools\Models\BaseModel;

trait AttachmentsTrait
{
    /**
     * @param BaseModel|Builder $model
     *
     * @return JsonResponse
     */
    public function uploadAttachments($model): JsonResponse
    {
        $request = $this->request;
        $request->validate($this->_uploadAttachmentsRules());
        $attachmentType = $request->input('attachment_type', '');
        $description = trans_has(($c = "attributes.$attachmentType")) ? $c : $attachmentType;
        $collection = request('collection', $model::$mediaAttachmentsCollection);
        try {
            $media = $model->addAttachment('attachment', $description, $collection, ['user_id' => auth(config('4myth-tools.auth_guard'))->id()]);
            if ($request->input('return') == 'current') {
                $resource = config('4myth-tools.media_resource_class');
                return $this->resource($resource::make($media));
            }
        }
        catch (Exception $exception) {
        }
        return $this->resource($this->getModelAttachmentsMedia($model), __("messages.uploaded_success"));
    }

    /**
     * @return array<string,array>
     */
    public function _uploadAttachmentsRules(): array
    {
        return [
            'attachment_type' => ['nullable'],
            'attachment'      => ['required', 'file'],
        ];
    }

    public function getModelAttachmentsMedia(&$model)
    {
        $model->refresh();
        $collection = request('collection', $model::$mediaAttachmentsCollection);
        $resource = config('4myth-tools.media_resource_class');
        return $resource::collection($model->getMedia($collection));
    }

    /**
     * @param BaseModel|Builder $model
     * @param BaseModel $media
     *
     * @return JsonResponse
     */
    public function deleteAttachment($model, $media): JsonResponse
    {
        if ($media->model->is($model)) {
            $media->delete();
        }
        return $this->resource($this->getModelAttachmentsMedia($model), __("messages.deleted_success"));
    }
}
