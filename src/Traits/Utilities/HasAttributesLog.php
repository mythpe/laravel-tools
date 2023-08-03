<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2022 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 */

namespace Myth\LaravelTools\Traits\Utilities;

use Exception;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait HasAttributesLog
{
    /**
     * @return void
     */
    protected static function bootHasAttributesLog(): void
    {
        static::saved(function (self $model) {
            if ($model->wasRecentlyCreated) {
                return !0;
            }
            $keys = $model->getChangeableAttributes();
            if ($model->wasChanged($keys)) {
                $changes = Arr::only($model->getChanges(), $keys);
                foreach ($changes as $attribute => $change) {
                    $oldValue = $model->getOriginal($attribute);
                    $newValue = $change;
                    if (Str::endsWith($attribute, '_id')) {
                        $relationName = Str::beforeLast($attribute, '_id');
                        $singular = Str::singular($relationName);
                        $cases = array_unique([
                            Str::camel($singular),
                            Str::snake($singular),
                            $singular,
                        ]);
                        foreach ($cases as $case) {
                            if (method_exists($model, $case)) {
                                if (($relation = $model->{$case}) && $relation->exists) {
                                    $relationClass = get_class($relation);
                                    if (($new = $relationClass::find($newValue)) && $new->{$model->getNameOfRelationColumn()}) {
                                        $newValue = $new->{$model->getNameOfRelationColumn()};
                                    }

                                    if (($old = $relationClass::find($oldValue)) && $old->{$model->getNameOfRelationColumn()}) {
                                        $oldValue = $old->{$model->getNameOfRelationColumn()};
                                    }
                                    break;
                                }
                            }
                        }
                    }
                    $model->createAttributeLog($attribute, $oldValue, $newValue, auth(config('4myth-tools.auth_guard'))->id());
                }
            }
            return !0;
        });
        static::deleted(function (self $model) {
            try {
                if (method_exists($model, 'isForceDeleting')) {
                    if ($model->isForceDeleting()) {
                        $model->allAttributesLogs()->forceDelete();
                    } else {
                        $model->attributesLogs()->delete();
                    }
                } else {
                    $model->allAttributesLogs()->forceDelete();
                }
            }
            catch (Exception) {
            }
        });
    }

    /**
     * @return array
     */
    public function getChangeableAttributes(): array
    {
        return $this->getFillable();
    }

    /**
     * @return string
     */
    public function getNameOfRelationColumn(): string
    {
        return 'name';
    }

    /**
     * @param $attribute
     * @param $oldValue
     * @param $newValue
     *
     * @return void
     */
    public function createAttributeLog($attribute, $oldValue, $newValue, $userId = null): void
    {
        try {

            $this->attributesLogs()->create([
                'user_id'   => $userId,
                'attribute' => $attribute,
                'old_value' => $oldValue,
                'new_value' => $newValue,
            ]);
        }
        catch (Exception) {

        }
    }

    /**
     * @return MorphMany
     */
    public function attributesLogs(): MorphMany
    {
        return $this->morphMany(config('4myth-tools.attributes_log_class'), config('4myth-tools.attributes_log_morph'));
    }

    /**
     * @return MorphMany
     */
    public function allAttributesLogs(): MorphMany
    {
        return $this->morphMany(config('4myth-tools.attributes_log_class'), config('4myth-tools.attributes_log_morph'))->withTrashed();
    }

    public function trashedAttributesLogs(): MorphMany
    {
        return $this->morphMany(config('4myth-tools.attributes_log_class'), config('4myth-tools.attributes_log_morph'))->onlyTrashed();
    }
}
