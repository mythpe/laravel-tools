<?php
/*
 * MyTh Ahmed Faiz Copyright © 2026. All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * GitHub: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

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
     * @var string|array|null
     */
    protected string | array | null $greeting = null;
    /**
     * The content will send via notification
     *
     * @var string|array|null
     */
    protected string | array | null $content = null;
    /**
     * The notification title
     * @var string|array|null
     */
    protected string | array | null $title = null;
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
    /**
     * Fcm image notification.
     * @var string|null
     */
    protected ?string $fcmImage = null;
    /**
     * Custom function fo FCM notifications.
     * @var array
     */
    protected array $customFcm = [];

    /**
     * @param array $via
     */
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
    public static function make(): static
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
     * @param object|null $notifiable
     *
     * @return array
     */
    public function getVia(object $notifiable = null): array
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
     * @param object $notifiable
     *
     * @return array
     */
    public function toArray(object $notifiable): array
    {
        return [
            'subject' => $this->getTitle($notifiable),
            'content' => $this->getContent($notifiable),
            'locale'  => $this->locale,
            ...$this->getData($notifiable),
        ];
    }

    /**
     * The title will send
     *
     * @param object $notifiable
     *
     * @return string
     */
    public function getTitle(object $notifiable): string
    {
        return $this->serializedProperty('title', $notifiable);
    }

    /**
     * @param string|array|null $value
     * @return $this
     */
    public function title(string | array | null $value): static
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
    public function getContent(object $notifiable): string
    {
        return $this->serializedProperty('content', $notifiable);
    }

    /**
     * @param string|array|null $value
     * @return $this
     */
    public function content(string | array | null $value): static
    {
        $this->content = $value ?: '';
        return $this;
    }

    /**
     * @param string $name
     * @param object $notifiable
     * @return string
     */
    public function serializedProperty(string $name, object $notifiable): string
    {
        if (!$this->{$name}) {
            return '';
        }
        if (is_array($this->{$name})) {
            $values = $this->{$name};
            return __($values[0] ?? '', $values[1] ?? [], $values[2] ?? $this->locale);
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
     * @param object $notifiable
     * @return array
     */
    public function getData(object $notifiable): array
    {
        return $this->data;
    }

    /**
     * @param object $notifiable
     *
     * @return SlackNotification
     */
    public function toSlack(object $notifiable): SlackNotification
    {
        return (new SlackNotification())->setContent($this->getContent($notifiable), $notifiable);
    }

    /**
     * @param object $notifiable
     *
     * @return MailMessage
     */
    public function toMail(object $notifiable): MailMessage
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
     * @param array|string|null $greeting
     *
     * @return $this
     */
    public function greeting(array | string | null $greeting): static
    {
        $this->greeting = $greeting;
        return $this;
    }

    /**
     * @param object $notifiable
     *
     * @return SmsNotification
     */
    public function toSms(object $notifiable): SmsNotification
    {
        return (new SmsNotification())->to($this->getMobile($notifiable))->content($this->getContent($notifiable));
    }

    /**
     * Get mobile number via sms
     *
     * @param object $notifiable
     *
     * @return string|string[]|mixed
     */
    public function getMobile(object $notifiable): mixed
    {
        if ($notifiable instanceof AnonymousNotifiable) {
            return $notifiable->routeNotificationFor(config('4myth-tools.sms.driver', 'sms'));
        }
        if ($notifiable instanceof Model) {
            if (method_exists($notifiable, 'routeNotificationForSms')) {
                return $notifiable->routeNotificationForSms($this);
            }
            return $notifiable->phone ?: $notifiable->mobile;
        }
        return null;
    }

    /**
     * @param object $notifiable
     *
     * @return ExpoPushNotification
     */
    public function toPushToken(object $notifiable): ExpoPushNotification
    {
        return (new ExpoPushNotification())
            ->channel($this->getPushTokenChannel($notifiable))
            ->to($this->getPushToken($notifiable))
            ->title($this->getTitle($notifiable))
            ->content($this->getContent($notifiable))
            ->data($this->getData($notifiable));
    }

    /**
     * @param $notifiable
     * @return FcmMessage
     */
    public function toFcm($notifiable): FcmMessage
    {
        return (new FcmMessage(notification : new FcmNotification(
            title : $this->getTitle($notifiable),
            body : $this->getContent($notifiable),
            image : $this->getFcmImage($notifiable)
        )))
            ->data($this->getData($notifiable))
            ->custom($this->getCustomFcm($notifiable));
    }

    /**
     * @param object $notifiable
     * @return string|null
     */
    public function getFcmImage(object $notifiable): ?string
    {
        return $this->fcmImage;
    }

    /**
     * @param string|null $image
     * @return $this
     */
    public function setFcmImage(?string $image): static
    {
        $this->fcmImage = $image;
        return $this;
    }

    /**
     * @param array $data
     *
     * @return $this
     */
    public function data(array $data): static
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Get token via push_token
     *
     * @param object $notifiable
     *
     * @return string|string[]|mixed
     */
    public function getPushToken(object $notifiable): mixed
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
     * @param object $notifiable
     * @return string
     */
    public function getPushTokenChannel(object $notifiable): string
    {
        return $this->pushTokenChannel;
    }

    /**
     * @param string $pushTokenChannel
     * @return $this
     */
    public function pushTokenChannel(string $pushTokenChannel): static
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

    /**
     * @param object $notifiable
     * @return array
     */
    public function getCustomFcm(object $notifiable): array
    {
        return $this->customFcm;
    }

    /**
     * @param array $customFcm
     * @return $this
     */
    public function setCustomFcm(array $customFcm): static
    {
        $this->customFcm = $customFcm;
        return $this;
    }
}
