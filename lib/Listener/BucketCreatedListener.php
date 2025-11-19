<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\FilesGCS\Listener;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Cloud\Storage\StorageClient;
use OCA\FilesGCS\Config;
use OCA\FilesGCS\Service\AutoclassEnablerService;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\Files\ObjectStore\Events\BucketCreatedEvent;

class BucketCreatedListener implements IEventListener {
	public function __construct(
		private Config $config
	) {}

	public function handle(Event $event): void {
		if (! $event instanceof BucketCreatedEvent) {
			return;
		}

		$endpoint = $event->getEndpoint();
		if (strpos($endpoint, 'storage.googleapis.com') === false) {
			return;
		}

		$credentials = json_decode($this->config->getCredentials(), true);
		$credentialsFetcher = new ServiceAccountCredentials(StorageClient::FULL_CONTROL_SCOPE, $credentials);

		$storage = new StorageClient([
			'credentialsFetcher' => $credentialsFetcher
		]);

		$bucket = $storage->bucket($event->getBucket());
		$bucket->update([
			'autoclass' => [
				'enabled' => $this->config->getAutoclassEnabled(),
				'terminalStorageClass' => strtoupper($this->config->getTerminalStorageClass())
			]
		]);
	}
}
