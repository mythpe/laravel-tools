<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2023 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Notifications;

class ExpoPushNotification
{
    /**
     * @var string
     */
    protected string $content = '';

    /**
     * @var string
     */
    protected string $title = '';

    /**
     * @var string|string[]
     */
    protected $pushToken = '';

    /**
     * Push notification channel
     *
     * @var string
     */
    protected string $channel = 'default';

    /**
     * Push notification data
     *
     * @var array
     */
    protected array $data = [];

    /**
     * @param string $title
     *
     * @return $this
     */
    public function title(string $title): self
    {
        $this->title = $title;
        return $this;
    }

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
     * @param string|string[] $pushToken
     *
     * @return $this
     */
    public function to($pushToken): self
    {
        $this->pushToken = $pushToken;
        return $this;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @return string|string[]
     */
    public function getPushToken()
    {
        return $this->pushToken;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getChannel(): string
    {
        return $this->channel;
    }

    /**
     * @param string $channel
     *
     * @return $this
     */
    public function channel(string $channel): self
    {
        $this->channel = $channel;
        return $this;
    }

    /**
     * @param array $data
     *
     * @return $this
     */
    public function data(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}
