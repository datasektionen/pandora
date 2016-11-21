<?php namespace App\Helpers;

use \App\Models\Event;

class EmailClient {
	/**
	 * The subject of the email to be sent.
	 * 
	 * @var string
	 */
	public $subject;

	/**
	 * The email address of the recipient.
	 * 
	 * @var string
	 */
	public $recipient;

	/**
	 * The email address of the sender. Must be verified by spam.
	 * 
	 * @var string
	 */
	public $sender;

	/**
	 * The HTML content of the email.
	 * 
	 * @var string
	 */
	public $html;

	/**
	 * Concats the given data to POST request valid format.
	 * 
	 * @param  array $data array of data to concat
	 * @return string
	 */
	public function concatData() {
        $data = [
            'to' => $this->recipient,
            'from' => $this->sender,
            'subject' => $this->subject,
            'html' => $this->html,
            'key' => env('SPAM_API_KEY')
        ];
		$res = "";
        foreach ($data as $key => $val) {
            $res .= $key . "=" . rawurlencode($val) . "&";
        }
        return $res;
	}

	/**
	 * Sends email with current $subject, $recipient, $sender and $html.
	 * 
	 * @return boolean
	 */
	public function send() {
		$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, env('SPAM_API_URL'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->concatData());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_exec($ch);
        curl_close ($ch);
        return true;
	}

	public static function sendBookingStatus(Event $event) {
		$email = new EmailClient;
		$email->recipient = $event->author->kth_username . "@kth.se";
		$email->sender = "no-reply@datasektionen.se";
		$email->subject = "Din bokning handläggs nu";
        $email->html = view('emails.reviewing')
            ->with('user', $event->author)
            ->with('event', $event)
            ->with('entity', $event->entity);
        $email->send();
	}

	public static function sendBookingConfirmation(Event $event) {
		$email = new EmailClient;
		$email->recipient = $event->author->kth_username . "@kth.se";
		$email->sender = "no-reply@datasektionen.se";
		$email->subject = "Din bokning är godkänd";
        $email->html = view('emails.approved')
            ->with('user', $event->author)
            ->with('event', $event)
            ->with('entity', $event->entity);
        $email->send();
	}

	public static function sendBookingDeclined(Event $event) {
		$email = new EmailClient;
		$email->recipient = $event->author->kth_username . "@kth.se";
		$email->sender = "no-reply@datasektionen.se";
		$email->subject = "Din bokning blev inte godkänd";
        $email->html = view('emails.declined')
            ->with('user', $event->author)
            ->with('event', $event)
            ->with('entity', $event->entity);
        $email->send();
	}

	public static function sendBookingNotification(Event $event) {
		$recipient = $event->entity->notify_email;
		if ($recipient === null) {
			return false;
		}
		if (!preg_match("/(.*@.*\..*)(,.*@.*\..*)*/", $recipient)) {
			return false;
		}
		$email = new EmailClient;
		$email->recipient = $recipient;
		$email->sender = "no-reply@datasektionen.se";
		$email->subject = "Ny bokningsförfrågan för " . $event->entity->name;
        $email->html = view('emails.notify')
            ->with('user', $event->author)
            ->with('event', $event)
            ->with('entity', $event->entity);
        $email->send();
	}
}