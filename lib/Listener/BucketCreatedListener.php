<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\FilesGCS\Listener;

use OCA\FilesGCS\Service\BucketService;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\Files\ObjectStore\Events\BucketCreatedEvent;

class BucketCreatedListener implements IEventListener {
	public function __construct(
		private BucketService $bucketService,
	) {
	}

	#[\Override]
	public function handle(Event $event): void {
		if (! $event instanceof BucketCreatedEvent) {
			return;
		}

		$endpoint = $event->getEndpoint();
		if (strpos($endpoint, 'storage.googleapis.com') === false) {
			return;
		}

		$bucketName = $event->getBucket();
		$this->bucketService->setAutoclassForBucket($bucketName, true);
	}
}
