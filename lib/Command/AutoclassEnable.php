<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
namespace OCA\FilesGCS\Command;

use OCA\FilesGCS\Config;
use OCA\FilesGCS\Services\AutoclassService;
use OCP\IConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
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
			->setDescription('Enable autoclass for new buckets');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$this->appConfig->setAutoclassEnabled(true);
		$output->writeln('New buckets will be created with autoclass enabled');

		$objectstore = $this->config->getSystemValue('objectstore');
		if (!is_array($objectstore)) {
			return Command::SUCCESS;
		}

		if (strpos($objectstore['arguments']['hostname'], 'storage.googleapis.com') === false) {
			return Command::SUCCESS;
		}

		$bucketName = $objectstore['arguments']['bucket'];
		if (empty($bucketName)) {
			return Command::SUCCESS;
		}

		$output->writeln('Detected bucket "' . $bucketName . '", enabling autoclass...');
		$credentials = $this->appConfig->getCredentials();
		if (empty($credentials)) {
			$output->writeln('Credentials not setup, skipping bucket update');
			return Command::SUCCESS;
		}

		$enabled = $this->service->enable($bucketName, $credentials);
		if (!$enabled) {
			$output->writeln('<error>Failed to enable autoclass for bucket ' . $bucketName . '</error>');
			return Command::FAILURE;
		}

		$output->writeln('<info>Autoclass for bucket ' . $bucketName . ' enabled</info>');

		return Command::SUCCESS;
	}
}
