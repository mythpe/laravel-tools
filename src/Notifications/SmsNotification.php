<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2024 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Notifications;

class SmsNotification
{
    /** @var ?string */
    protected ?string $content = null;

    /** @var string|string[]|null */
    protected string | array | null $mobile = null;

    /**
     * Set the content of the message.
     *
     * @param string $content
     *
     * @return $this
     */
    public function content(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Set the receiver of the message.
     *
     * @param string|string[] $mobile
     *
     * @return $this
     */
    public function to(array | string | null $mobile): self
    {
        $this->mobile = $mobile ?: null;
        return $this;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content ?: '';
    }

    /**
     * @return array|string|null
     */
    public function getMobile(): array | string | null
    {
        return $this->mobile;
    }
}
