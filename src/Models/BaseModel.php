<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2024 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Models;

use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticate;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Myth\LaravelTools\Traits\BaseModel\BaseModelTrait;
use Myth\LaravelTools\Traits\BaseModel\HasMediaTrait;
use Myth\LaravelTools\Traits\BaseModel\SlugModelTrait;
use Spatie\MediaLibrary\HasMedia;


/**
 *
 * @property array|mixed|string|void|null $id
 * @property array|mixed|string|void|null $created_at
 * @property array|mixed|string|void|null $updated_at
 * @property-read string ${DATE_ATTRIBUTE}_to_readable_format
 * @property-read string ${ATTRIBUTE}_to_number_format
 * @example
 * $this->date_to_readable_format
 */
class BaseModel extends Authenticate implements HasMedia, HasLocalePreference
{
    use HasFactory;
    use Notifiable;
    use HasMediaTrait;
    use SlugModelTrait;
    use BaseModelTrait;

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $name = locale_attribute();
        if ($this->isFillable($name) && !$this->isFillable('name')) {
            $this->append(['name']);
        }
        $this->makeHidden($this->defaultHiddenAttributes());
    }
}
