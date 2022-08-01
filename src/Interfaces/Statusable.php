<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2022 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 */

namespace Myth\LaravelTools\Interfaces;

use Illuminate\Support\Collection;

interface Statusable
{
    /** @var string */
    const ACTIVE_STATUS = 'active';

    /** @var string */
    const INACTIVE_STATUS = 'inactive';

    /** @var string */
    const PENDING_STATUS = 'pending';

    /** @var string */
    const APPROVED_STATUS = 'approved';

    /** @var string */
    const CANCELED_STATUS = 'canceled';

    /** @var string */
    const USED_STATUS = 'used';

    /** @var string */
    const BANDED_STATUS = 'banded';

    /** @var string */
    const UNPAID_STATUS = 'unpaid';

    /** @var string */
    const PAID_STATUS = 'paid';

    /** @var string */
    const FINISHED_STATUS = 'finished';

    /** @var string */
    const ACTIVATED_STATUS = 'activated';

    /** @var string */
    const CONFIRMED_STATUS = 'confirmed';

    /** @var string */
    const UNCONFIRMED_STATUS = 'unconfirmed';

    /** @var string */
    const NEW_STATUS = 'new';

    /** @var string */
    const ARCHIVED_STATUS = 'archived';

    /** @var string */
    const COMPLETED_STATUS = 'completed';

    /** @var string */
    const REJECTED_STATUS = 'rejected';

    /**
     * @return \Illuminate\Support\Collection
     */
    public static function getStatuses(): Collection;

    /**
     * @return array
     */
    public static function getStatusesCodes(): array;

    /**
     * @param $status
     *
     * @return string|null
     */
    public static function getStatus($status): ?string;

    /**
     * @return string
     */
    public function getStatusToStringAttribute(): string;

}
