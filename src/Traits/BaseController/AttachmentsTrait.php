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

        $attachmentType = $request->input('attachment_type', null);
        $attachmentType = $attachmentType ? (trans_has(($c = "attributes.$attachmentType"), null, !0) ? $c : $attachmentType) : null;

        $description = $request->input('description', null);
        $description = $description ? (trans_has(($c = "attributes.$description"), null, !0) ? $c : $description) : null;

        $collection = $request->input('collection', $model::$mediaAttachmentsCollection);
        try {
            $opts = [];
            if ($id = auth(config('4myth-tools.auth_guard'))->id()) {
                $opts['user_id'] = $id;
            }
            if ($id = $request->input('attachment_type_id')) {
                $opts['attachment_type_id'] = $id;
            }
            $media = $model->addAttachment('attachment', $description, $collection, $opts);
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

    /**
     * @param BaseModel $model
     * @return mixed
     */
    public function getModelAttachmentsMedia(&$model)
    {
        $model->refresh();
        $request = $this->request;
        $collection = $request->input('collection', $model::$mediaAttachmentsCollection);
        $resource = config('4myth-tools.media_resource_class');
        $media = $request->input('return') != 'all' ? $model->getMedia($collection)->sortByDesc('order_column') : $model->media()->latest('order_column')->get();
        return $resource::collection($media);
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
