<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\FilesGCS\Command;

use OCA\FilesGCS\ConfigLexicon;
use OCA\FilesGCS\Exceptions\BucketMissingException;
use OCA\FilesGCS\Service\BucketService;
use OCP\AppFramework\Services\IAppConfig;
use OCP\Console\Attribute\AsCommand;
use OCP\Console\Attribute\Option;
use OCP\Console\ExitCode;
use OCP\Console\IOutput;
use OCP\IConfig;

class AutoclassCommands {

	public function __construct(
		private IAppConfig $appConfig,
		private IConfig $config,
		private BucketService $bucketService,
	) {
	}

	#[AsCommand(
		name: 'files_gcs:autoclass:enable',
		description: 'Enable autoclass for new buckets. If the objectstore and bucket are specified, '
					 . 'an attempt to enable autoclass for the bucket will be initiated.'
	)]
	public function enable(
		IOutput $output,
		#[Option(name: 'object-store')]
		?string $objectStore = null,
		#[Option]
		?string $bucket = null,
	): ExitCode {
		if (!$this->appConfig->getAppValueBool(ConfigLexicon::AUTOCLASS_ENABLED)) {
			$this->appConfig->setAppValueBool(ConfigLexicon::AUTOCLASS_ENABLED, true);
			$output->writeln('New buckets will be created with autoclass enabled');
		}

		if ($objectStore === null) {
			return ExitCode::Success;
		}

		/** @var ?array $objectstores */
		$objectstores = $this->config->getSystemValue('objectstore');
		if (!isset($objectstores[$objectStore])) {
			$output->writeln('<comment>No configuration found for object store ' . $objectStore . '</comment>');
			return ExitCode::Failure;
		}

		/** @var ?array $config */
		$config = $objectstores[$objectStore];
		if (isset($config['arguments']['hostname'])
			&& strpos($config['arguments']['hostname'], 'storage.googleapis.com') === false) {
			return ExitCode::Failure;
		}

		if ($bucket === null) {
			$output->writeln('<comment>No bucket specified. Specify a bucket with --bucket to enable autoclass for it</comment>');
			return ExitCode::Failure;
		}

		$output->writeln('Enabling autloclass for ' . $bucket . '...');

		try {
			$enabled = $this->bucketService->setAutoclassForBucket($bucket, true);
		} catch (BucketMissingException $exception) {
			$output->writeln('<comment>Bucket ' . $bucket . ' does not yet exist. Autoclass will be enabled when it is created</comment>');
			return ExitCode::Failure;
		}

		if (!$enabled) {
			$output->writeln('<error>Failed to enable autoclass for bucket ' . $bucket . '</error>');
			return ExitCode::Failure;
		}

		$output->writeln('<info>Autoclass for bucket ' . $bucket . ' enabled</info>');

		return ExitCode::Success;
	}

	#[AsCommand(
		name: 'files_gcs:autoclass:disable',
		description: 'Disable automatic autoclass activation for new buckets'
	)]
	public function disable(IOutput $output): ExitCode {
		$this->appConfig->setAppValueBool(ConfigLexicon::AUTOCLASS_ENABLED, false);
		$output->writeln('Autoclass disabled');

		return ExitCode::Success;
	}
}
