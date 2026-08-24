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
use OCP\IConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AutoclassEnable extends Command {

	public function __construct(
		private IAppConfig $appConfig,
		private IConfig $config,
		private BucketService $bucketService,
	) {
		parent::__construct();
	}

	#[\Override]
	protected function configure(): void {
		$this->setName('files_gcs:autoclass:enable')
			->setDescription('Enable autoclass for new buckets. If the objectstore and bucket are specified, an '
							 . ' attempt to enable autoclass for the bucket will be initiated.')
			->addOption('object-store', 'o', InputOption::VALUE_REQUIRED, 'The name of the objectstore')
			->addOption('bucket', 'b', InputOption::VALUE_REQUIRED, 'The name of the bucket');
	}

	#[\Override]
	protected function execute(InputInterface $input, OutputInterface $output): int {
		if (!$this->appConfig->getAppValueBool(ConfigLexicon::AUTOCLASS_ENABLED)) {
			$this->appConfig->setAppValueBool(ConfigLexicon::AUTOCLASS_ENABLED, true);
			$output->writeln('New buckets will be created with autoclass enabled');
		}

		/** @var ?string $objectstoreName */
		$objectstoreName = $input->getOption('object-store');
		if ($objectstoreName === null) {
			return Command::SUCCESS;
		}

		/** @var ?array $objectstores */
		$objectstores = $this->config->getSystemValue('objectstore');
		if (!isset($objectstores[$objectstoreName])) {
			$output->writeln('<comment>No configuration found for object store ' . $objectstoreName . '</comment>');
			return Command::SUCCESS;
		}

		/** @var ?string $bucketName */
		$bucketName = $input->getOption('bucket');
		if ($bucketName === null) {
			return Command::SUCCESS;
		}

		/** @var ?array $objectstore */
		$objectstore = $objectstores[$objectstoreName];
		if (isset($objectstore['arguments']['hostname'])
			&& strpos($objectstore['arguments']['hostname'], 'storage.googleapis.com') === false) {
			return Command::SUCCESS;
		}

		$output->writeln('Enabling autloclass for ' . $bucketName . '...');

		try {
			$enabled = $this->bucketService->setAutoclassForBucket($bucketName, true);
		} catch (BucketMissingException $exception) {
			$output->writeln('<comment>Bucket ' . $bucketName . ' does not yet exist. Autoclass will be enabled when it is created</comment>');
			return Command::SUCCESS;
		}

		if (!$enabled) {
			$output->writeln('<error>Failed to enable autoclass for bucket ' . $bucketName . '</error>');
			return Command::FAILURE;
		}

		$output->writeln('<info>Autoclass for bucket ' . $bucketName . ' enabled</info>');

		return Command::SUCCESS;
	}
}
