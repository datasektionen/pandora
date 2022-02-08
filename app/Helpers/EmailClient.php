<?php namespace App\Helpers;

use App\Models\Event;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\View\View;
use Throwable;

class EmailClient
{
    /**
     * The subject of the email to be sent.
     * @var string
     */
    public $subject;

    /**
     * The email address of the recipient.
     * @var string
     */
    public $recipient;

    /**
     * The email address of the sender. Must be verified by spam.
     * @var string
     */
    public $sender;

    /**
     * The HTML content of the email.
     * @var View
     */
    public $html;

    public function __construct(string $subject)
    {
        $this->sender = 'no-reply@datasektionen.se';
        $this->subject = $subject;
    }

    /**
     * Sends email with current $subject, $recipient, $sender and $html.
     *
     * Possible to fail to send if:
     * - No recipient set
     * - Recipient is not an email address
     * - Failed to render template
     * - Error in POST request to email system
     *
     * @return boolean
     */
    public function send(): bool
    {
        if ($this->recipient === null) {
            return false;
        }

        if (!preg_match('/(.*@.*\..*)(,.*@.*\..*)*/', $this->recipient)) {
            return false;
        }

        $client = new Client();
        try {
            $response = $client->post(config('spam.url'), [
                'json' => [
                    'to' => $this->recipient,
                    'from' => $this->sender,
                    'subject' => $this->subject,
                    'html' => $this->html->render(),
                    'key' => config('spam.api_key'),
                ],
            ]);
            return $response->getStatusCode() == 200;
        } catch (Throwable | GuzzleException $e) {
            return false;
        }
    }

    public static function sendBookingStatus(Event $event): bool
    {
        $email = new self('Din bokning handläggs nu');
        $email->recipient = $event->author->kth_username . '@kth.se';
        $email->html = view('emails.reviewing')
            ->with('user', $event->author)
            ->with('event', $event)
            ->with('entity', $event->entity);
        return $email->send();
    }

    public static function sendBookingConfirmation(Event $event): bool
    {
        $email = new self('Din bokning är godkänd');
        $email->recipient = $event->author->kth_username . '@kth.se';
        $email->html = view('emails.approved')
            ->with('user', $event->author)
            ->with('event', $event)
            ->with('entity', $event->entity);
        return $email->send();
    }

    public static function sendBookingDeclined(Event $event): bool
    {
        $email = new self('Din bokning blev inte godkänd');
        $email->recipient = $event->author->kth_username . '@kth.se';
        $email->html = view('emails.declined')
            ->with('user', $event->author)
            ->with('event', $event)
            ->with('entity', $event->entity);
        return $email->send();
    }

    public static function sendBookingNotification(Event $event): bool
    {
        $email = new self("Ny bokningsförfrågan för {$event->entity->name}");
        $email->recipient = $event->entity->notify_email;
        $email->html = view('emails.notify')
            ->with('user', $event->author)
            ->with('event', $event)
            ->with('entity', $event->entity);
        return $email->send();
    }

    public static function sendBookingChanged(Event $oldEvent, Event $event, array $dirty): bool {
        $email = new self("Din bokning av {$event->entity->name} ändrades");
        $email->recipient = $event->author->kth_username . '@kth.se';
        $email->html = view('emails.changed')
            ->with('user', $event->author)
            ->with('event', $event)
            ->with('oldEvent', $oldEvent)
            ->with('entity', $event->entity)
            ->with('dirty', $dirty);

        return $email->send();
    }

    public static function sendBookingChangedNotification(Event $oldEvent, Event $event, array $dirty): bool {
        $email = new self(
            "Bokningen {$event->entity->name} ändrades och måste granskas"
        );
        $email->recipient = $event->entity->notify_email;
        $email->html = view('emails.changed-notify')
            ->with('event', $event)
            ->with('oldEvent', $oldEvent)
            ->with('entity', $event->entity)
            ->with('dirty', $dirty);
        return $email->send();
    }

    public static function sendBookingDeleted(Event $event): bool
    {
        $email = new self("Bokningen {$event->entity->name} togs bort");
        $email->recipient = $event->entity->notify_email;
        $email->html = view('emails.deleted')
            ->with('event', $event)
            ->with('entity', $event->entity);
        $sentToEntityOwner = $email->send();

        $email->recipient = $event->author->kth_username . '@kth.se';
        $sentToAuthor = $email->send();

        return $sentToEntityOwner && $sentToAuthor;
    }
}
