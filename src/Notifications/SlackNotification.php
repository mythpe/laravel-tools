<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2022 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 */

namespace Myth\LaravelTools\Notifications;

use Illuminate\Notifications\Messages\SlackMessage;

class SlackNotification extends SlackMessage
{

    /**
     * @param  string  $content
     * @param $notifiable
     *
     * @return $this
     */
    public function setContent(string $content, $notifiable): self
    {
        $this->content = setting(locale_attribute('app_name')).PHP_EOL;
        $this->content .= $content;
        return $this;
    }
}
