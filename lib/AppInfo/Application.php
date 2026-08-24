<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\FilesGCS\AppInfo;

use OCA\FilesGCS\ConfigLexicon;
use OCA\FilesGCS\Listener\BucketCreatedListener;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\Files\ObjectStore\Events\BucketCreatedEvent;

require_once __DIR__ . '/../../vendor/autoload.php';

class Application extends App implements IBootstrap {
	public const string APP_ID = 'files_gcs';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}

	#[\Override]
	public function register(IRegistrationContext $context): void {
		$context->registerConfigLexicon(ConfigLexicon::class);
		$context->registerEventListener(BucketCreatedEvent::class, BucketCreatedListener::class);
	}

	#[\Override]
	public function boot(IBootContext $context): void {
	}
}
