<?php


class NotifHandler {

	private $devRant;

	private $store;

	private $themes;

	public function __construct ($store, $devRant) {
		$this->store = $store;
		$this->devRant = $devRant;
	}

	private function log ($msg) {
		if (DEBUG) echo 'NotifHandler > ' . $msg . PHP_EOL;
	}


	public function handleHereNotif ($rantID, $userID) {
		if ($this->devRant->getMyUserID() === $userID)
			return;

		$rant = $this->devRant->getRant($rantID);
		if (!$rant) return;

		if ($rant['rant']['user_id'] !== $userID) {
			$this->log('User is not the OP.');

			return;
		}

		$mentionList = [];

		foreach ($rant['comments'] as $comment) {
			if (in_array($comment['user_username'], MENTION_BLACKLIST))
				continue;

			if ($comment['user_id'] === $userID)
				continue;

			if (in_array($comment['user_username'], $mentionList))
				continue;

			$mentionList[] = $comment['user_username'];
		}

		if (empty($mentionList))
			return;


		$msg = '';

		sort($mentionList); // Why not sort it?

		foreach ($mentionList as $mention) {
			$msg .= "@$mention ";
		}

		$msg .=
			"\n\nUser @" .
			$rant['rant']['user_username'] .
			" requested to mention everyone in this comment section. He/She probably has something important to tell...\nLook above!";

		$this->devRant->postComment($rantID, $msg);
	}
}