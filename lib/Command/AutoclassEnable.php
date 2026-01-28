<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
namespace OCA\FilesGCS\Command;

use OCA\FilesGCS\Config;
use OCA\FilesGCS\Services\AutoclassService;
use OCA\FilesGCS\Exceptions\BucketMissingException;
use OCP\IConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AutoclassEnable extends Command {

	public function __construct(
		private Config $appConfig,
		private IConfig $config,
		private AutoclassService $service,
	) {
		parent::__construct();
	}

	protected function configure(): void {
		$this->setName('files_gcs:autoclass:enable')
			->setDescription('Enable autoclass for new buckets. If the objectstore and bucket are specified, an '
							 . ' attempt to enable autoclass for the bucket will be initiated.')
			->addOption('object-store', 'o', InputOption::VALUE_REQUIRED , 'The name of the objectstore')
			->addOption('bucket', 'b', InputOption::VALUE_REQUIRED, 'The name of the bucket');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$this->appConfig->setAutoclassEnabled(true);
		$output->writeln('New buckets will be created with autoclass enabled');

		if (!($objectstoreName = $input->getOption('object-store'))) {
			return Command::SUCCESS;
		}

		$objectstores = $this->config->getSystemValue('objectstore');
		if (!isset($objectstores[$objectstoreName])) {
			$output->writeln('<comment>No configuration found for object store ' . $objectstoreName . '</comment>');
			return Command::SUCCESS;
		}

		if (!($bucketName = $input->getOption('bucket'))) {
			return Command::SUCCESS;
		}

		$objectstore = $objectstores[$objectstoreName];
		if (strpos($objectstore['arguments']['hostname'], 'storage.googleapis.com') === false) {
			return Command::SUCCESS;
		}

		$credentials = $this->appConfig->getCredentials();
		if (empty($credentials)) {
			$output->writeln('<comment>Credentials not setup, skipping bucket update</comment>');
			return Command::SUCCESS;
		}

		$output->writeln('Enabling autloclass for ' . $bucketName . '...');

		try {
			$enabled = $this->service->enable($bucketName, $credentials);
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
