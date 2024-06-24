<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2024 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Traits\BaseController;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Myth\LaravelTools\Models\BaseModel;

trait ModelMediaTrait
{
    static string $returnTypeKey = 'return';

    /**
     * @param BaseModel|Builder $model
     *
     * @return JsonResponse
     */
    public function uploadAttachments($model): JsonResponse
    {
        $request = $this->request;
        $rules = $this->_uploadAttachmentsRules();
        $request->validate($rules);
        $collection = $request->input('collection', $model::$mediaAttachmentsCollection);

        $description = $request->input('description', null);
        $description = $description ? (trans_has(($c = "attributes.$description"), null, !0) ? $c : $description) : null;

        try {
            $mediaClass = config('media-library.media_model');
            $_media = new $mediaClass();
            $opts = $request->only($_media->getFillable());
            foreach ($this->_uploadAttachmentsProperties() as $key) {
                if ($v = $request->input($key, $opts[$key] ?? null)) {
                    $opts[$key] = $v;
                }
            }

            if ($id = auth(config('4myth-tools.auth_guard'))->id()) {
                $opts['user_id'] = $id;
            }

            $media = $model->addAttachment('attachment', $description, $collection, $opts);
            if ($request->input(static::$returnTypeKey) == 'current') {
                $resource = config('4myth-tools.media_resource_class');
                return $this->resource($resource::make($media));
            }
        }
        catch (Exception $exception) {
            return $this->errorResponse($exception->getMessage());
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
     * @return array<int,string>
     */
    public function _uploadAttachmentsProperties(): array
    {
        return [];
    }

    /**
     * Example: Collection
     *  [ 'collection' => 'files' ]
     * Example: Return all attachments
     * [ static::$returnType => 'all' ]
     * @param BaseModel|Model $model
     * @return AnonymousResourceCollection
     * @uses self::$returnTypeKey
     */
    public function getModelAttachmentsMedia(&$model): AnonymousResourceCollection
    {
        $model->refresh();
        $request = $this->request;
        $resource = config('4myth-tools.media_resource_class');
        if ($request->input(self::$returnTypeKey) == 'all') {
            return $resource::collection($model->media()->latest('order_column')->get());
        }
        return $resource::collection($model->getMediaAttachments($request->input('collection')));
    }

    /**
     * @param Model $model
     * @param Model $media
     * @return JsonResponse
     */
    public function deleteAttachment(Model $model, Model $media): JsonResponse
    {
        if ($media->model->is($model)) {
            $media->delete();
        }
        return $this->resource($this->getModelAttachmentsMedia($model), __("messages.deleted_success"));
    }
}
