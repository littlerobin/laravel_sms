<?php namespace App\Services;


class SlackNotificationService{

	/**
	 * Notify us about the new registrations, purchases and pending orders
	 *
	 * @param string $text
	 * @return void
	 */
	public static function notify($text)
	{
		if(!config('notifications.enable_slack_notification')) {return;}
		try{
			$client = new \Maknz\Slack\Client('https://hooks.slack.com/services/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');
			$client->send($text);
		} catch (\Exception $e) {
			\Log::error($e);
			return false;
		}
		
	}

}

