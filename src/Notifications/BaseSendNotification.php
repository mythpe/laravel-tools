<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2022 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 */

namespace Myth\LaravelTools\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BaseSendNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * via notification
     *
     * @var array
     */
    public array $via = [];

    /**
     * The notification's greeting.
     *
     * @var string
     */
    public string | array | null $greeting = null;

    /**
     * The content will send via notification
     *
     * @var string
     */
    protected string | array | null $content = '';

    /**
     * The notification title
     *
     * @var string
     */
    protected string | array | null $title = '';

    /**
     * The channel of push notification
     *
     * @var string
     */
    protected string $pushTokenChannel = 'default';

    /**
     * Notification Data
     *
     * @var array
     */
    protected array $data = [];

    public function __construct(array $via = [])
    {
        if (empty($via) && method_exists(config('4myth-tools.setting_class'), 'getNotificationMethods')) {
            $via = config('4myth-tools.setting_class')::getNotificationMethods();
        }
        $this->via = array_unique($via);
    }

    /**
     * @return static
     */
    public static function make(): self
    {
        return new self(...func_get_args());
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function via($notifiable): array
    {
        if ($notifiable instanceof AnonymousNotifiable) {
            return array_keys($notifiable->routes);
        }
        //return ['sms'];
        //d($this->via);
        return $this->getVia($notifiable);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function getVia($notifiable): array
    {
        return collect($this->via)->unique()->filter()->values()->toArray();
    }

    /**
     * @param $notifiable
     *
     * @return array
     */
    public function toArray($notifiable): array
    {
        return array_merge([
            'subject' => $this->getTitle($notifiable),
            'content' => $this->getContent($notifiable),
        ], $this->getData($notifiable));
    }

    /**
     * The title will send
     *
     * @param $notifiable
     *
     * @return string
     */
    public function getTitle($notifiable): string
    {
        return (is_array($this->title) ? __(...$this->title) : (trans_has($this->title) ? __($this->title) : $this->title)) ?: '';
    }

    /**
     * @param string|array|null $title
     *
     * @return $this
     */
    public function setTitle(string | array | null $title): self
    {
        $this->title = $title ?: '';
        return $this;
    }

    /**
     * The full message will send
     *
     * @param $notifiable
     *
     * @return string
     */
    public function getContent($notifiable): string
    {
        return (is_array($this->content) ? __(...$this->content) : (trans_has($this->content) ? __($this->content) : $this->content)) ?: '';
    }

    /**
     * @param string|array|null $content
     *
     * @return $this
     */
    public function setContent(string | array | null $content): self
    {
        $this->content = $content ?: '';
        return $this;
    }

    /**
     * @return array
     */
    public function getData($notifiable): array
    {
        return $this->data;
    }

    /**
     * @param $notifiable
     *
     * @return SlackNotification
     */
    public function toSlack($notifiable): SlackNotification
    {
        return (new SlackNotification())->setContent($this->getContent($notifiable), $notifiable);
    }

    /**
     * @param $notifiable
     *
     * @return MailMessage|mixed
     */
    public function toMail($notifiable)
    {
        $mail = new MailMessage();
        $mail->subject($this->getTitle($notifiable));
        //$content = $this->getContent($notifiable);
        $content = nl2br($this->getContent($notifiable));

        //$breaks = ["<br>", "<br >", "<br />", "<br/>"];
        //$s = "\n";
        //$content = str_ireplace($breaks, PHP_EOL, $content);
        //$content = str_ireplace(PHP_EOL, $s, $content);
        //$lines = explode($s, $content);
        $lines = explode("<br />", $content);

        if ($this->greeting) {
            $greeting = is_array($this->greeting) ? __(...$this->greeting) : (trans_has($this->greeting) ? __($this->greeting) : $this->greeting);
            $mail->greeting($greeting);
        }
        //d($lines);
        foreach ($lines as $line) {
            $mail->line($line);
        }
        return $mail;
    }

    /**
     * Set the greeting of the notification.
     *
     * @param string|array $greeting
     *
     * @return $this
     */
    public function greeting($greeting)
    {
        $this->greeting = $greeting;

        return $this;
    }

    /**
     * @param $notifiable
     *
     * @return SmsNotification
     */
    public function toSms($notifiable): SmsNotification
    {
        return (new SmsNotification())->to($this->getMobile($notifiable))->content($this->getContent($notifiable));
    }

    /**
     * Get mobile number via sms
     *
     * @param $notifiable
     *
     * @return string|string[]|mixed
     */
    public function getMobile($notifiable)
    {
        if ($notifiable instanceof AnonymousNotifiable) {
            return $notifiable->routeNotificationFor('sms');
        }
        if ($notifiable instanceof Model) {
            if (method_exists($notifiable, 'getNotificationMobile')) {
                return $notifiable->getNotificationMobile($this);
            }
            return $notifiable->mobile;
        }
        return null;
    }

    /**
     * @param $notifiable
     *
     * @return ExpoPushNotification
     */
    public function toPushToken($notifiable): ExpoPushNotification
    {
        return (new ExpoPushNotification())
            ->to($this->getPushToken($notifiable))
            ->title($this->getTitle($notifiable))
            ->channel($this->getPushTokenChannel($notifiable))
            ->content($this->getContent($notifiable))
            ->data($this->getData($notifiable));
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
     * Get token via push_token
     *
     * @param $notifiable
     *
     * @return string|string[]|mixed
     */
    public function getPushToken($notifiable)
    {
        if ($notifiable instanceof AnonymousNotifiable) {
            return $notifiable->routeNotificationFor('push_token');
        }
        if ($notifiable instanceof Model) {
            if (method_exists($notifiable, 'getNotificationPushToken')) {
                return $notifiable->getNotificationPushToken($this);
            }
            return $notifiable->push_token;
        }
        return $notifiable;
    }

    /**
     * @param $notifiable
     *
     * @return string
     */
    public function getPushTokenChannel($notifiable): string
    {
        return $this->pushTokenChannel;
    }

    /**
     * @param string $pushTokenChannel
     */
    public function setPushTokenChannel(string $pushTokenChannel): void
    {
        $this->pushTokenChannel = $pushTokenChannel;
    }

    /**
     * Determine which queues should be used for each notification channel.
     *
     * @return array
     */
    public function viaQueues(): array
    {
        return [
            'mail'  => 'default',
            'slack' => 'default',
            'sms'   => 'default',
        ];
    }
}
