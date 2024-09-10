<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2024 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Notifications;

use Closure;
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
    protected array $via = [];
    /**
     * The notification's greeting.
     *
     * @var string
     */
    protected string | array | Closure | null $greeting = null;
    /**
     * The content will send via notification
     *
     * @var string|array|Closure|null
     */
    protected string | array | Closure | null $content = null;
    /**
     * The notification title
     * @var string|array|Closure|null
     */
    protected string | array | Closure | null $title = null;
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
    public function via(object $notifiable): array
    {
        if ($notifiable instanceof AnonymousNotifiable) {
            return array_keys($notifiable->routes);
        }
        return $this->getVia($notifiable);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed|null $notifiable
     *
     * @return array
     */
    public function getVia(mixed $notifiable = null): array
    {
        return $this->via;
    }

    /**
     * @param $via
     * @return $this
     */
    public function setVia($via): static
    {
        $this->via = collect((array) $via)->unique()->filter()->values()->toArray();
        return $this;
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
        return $this->serializedProperty('title', $notifiable);
    }

    /**
     * @param string|array|Closure|null $value
     * @return $this
     */
    public function title(string | array | Closure | null $value): self
    {
        $this->title = $value ?: '';
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
        return $this->serializedProperty('content', $notifiable);
    }

    /**
     * @param string|array|null $content
     *
     * @return $this
     */
    public function content(string | array | Closure | null $value): self
    {
        $this->content = $value ?: '';
        return $this;
    }

    /**
     * @param $name
     * @param $notifiable
     * @return string
     */
    public function serializedProperty($name, $notifiable = null): string
    {
        if (!$this->{$name}) {
            return '';
        }
        if (is_array($this->{$name})) {
            return __(...$this->{$name});
        }
        if (is_callable($this->{$name})) {
            return call_user_func($this->{$name}, $notifiable);
        }
        if (trans_has($this->{$name}, $this->locale)) {
            return __($this->{$name}, [], $this->locale);
        }
        return (string) $this->{$name};
    }

    /**
     * @param $notifiable
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
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $mail = new MailMessage();
        $mail->subject($this->getTitle($notifiable));
        $content = nl2br($this->getContent($notifiable));
        $lines = explode("<br />", $content);
        if ($this->greeting) {
            $greeting = $this->serializedProperty('greeting', $notifiable);
            $mail->greeting($greeting);
        }
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
    public function greeting($greeting): self
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
    public function getMobile($notifiable): mixed
    {
        if ($notifiable instanceof AnonymousNotifiable) {
            return $notifiable->routeNotificationFor(config('4myth-tools.sms.driver', 'sms'));
        }
        if ($notifiable instanceof Model) {
            if (method_exists($notifiable, 'routeNotificationForSms')) {
                return $notifiable->routeNotificationForSms($this);
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
            ->channel($this->getPushTokenChannel($notifiable))
            ->to($this->getPushToken($notifiable))
            ->title($this->getTitle($notifiable))
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
    public function getPushToken($notifiable): mixed
    {
        if ($notifiable instanceof AnonymousNotifiable) {
            return $notifiable->routeNotificationFor(config('4myth-tools.push_token.driver', 'push_token'));
        }
        if ($notifiable instanceof Model) {
            if (method_exists($notifiable, 'routeNotificationForPushToken')) {
                return $notifiable->routeNotificationForPushToken($this);
            }
            return $notifiable?->push_token;
        }
        return $notifiable;
    }

    /**
     * @param $notifiable
     * @return string
     */
    public function getPushTokenChannel($notifiable): string
    {
        return $this->pushTokenChannel;
    }

    /**
     * @param string $pushTokenChannel
     * @return $this
     */
    public function pushTokenChannel(string $pushTokenChannel): self
    {
        $this->pushTokenChannel = $pushTokenChannel;
        return $this;
    }

    /**
     * Determine which queues should be used for each notification channel.
     *
     * @return array
     */
    public function viaQueues(): array
    {
        return [
            'database'                                            => 'default',
            'mail'                                                => 'default',
            'slack'                                               => 'default',
            config('4myth-tools.sms.driver', 'sms')               => 'default',
            config('4myth-tools.push_token.driver', 'push_token') => 'default',
            config('4myth-tools.whatsapp.driver', 'whatsapp')     => 'default',
        ];
    }
}
