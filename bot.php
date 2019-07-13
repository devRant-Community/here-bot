<?php

// This file gets executed every few seconds and handles everything

require_once 'config.php';

require_once 'lib/Store.php';
require_once 'lib/DevRant.php';

function botLog ($msg) {
	if (DEBUG) echo 'Bot > ' . $msg . PHP_EOL;
}

$store = new Store('./store', ['prettify' => true, 'log' => DEBUG]);
$devRant = new DevRant($store);

$notifications = $devRant->getNotifications();
$devRant->clearNotifications();

usort($notifications['items'], function ($a, $b) {
	return $a['created_time'] <=> $b['created_time'];
});

require_once 'lib/NotifHandler.php';

$notifHandler = new NotifHandler($store, $devRant);

$didSomething = false;

if (!isset($store('misc')['lastNotifTime']))
	$store('misc')['lastNotifTime'] = time();

$lastNotifTime = $store('misc')['lastNotifTime'];
$newLastNotifTime = 0;

foreach ($notifications['items'] as $notification) {
	if ($notification['type'] !== 'comment_mention')
		continue;

	if ($notification['created_time'] > $lastNotifTime || $notification['read'] === 0) {
		botLog('Handling a here request notif (RantID: ' . $notification['rant_id'] . ')...');
		$notifHandler->handleHereNotif($notification['rant_id'], $notification['uid']);

		$didSomething = true;
		$newLastNotifTime = $notification['created_time'];
	}
}

if ($didSomething) {
	$store('misc')['lastNotifTime'] = $newLastNotifTime;
} else {
	botLog('Nothing to do...');
}