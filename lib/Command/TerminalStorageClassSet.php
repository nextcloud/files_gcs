<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
namespace OCA\FilesGCS\Command;

use OCA\FilesGCS\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TerminalStorageClassSet extends Command {
	public function __construct(
		private Config $config,
	) {
		parent::__construct();
	}

	protected function configure(): void {
		$this->setName('files_gcs:terminal_storage_class:set')
			->setDescription('Set the terminal storage class for new buckets. Defaults to Nearline')
			->addArgument(
				'storage',
				InputArgument::REQUIRED,
				'Can be set to "Nearline" (default) or "Archive"'
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$storageClass = $input->getArgument('storage');

		if (strtolower($storageClass) !== 'nearline' && strtolower($storageClass) !== 'archive') {
			$output->writeln('Invalid storage class. Terminal storage class must be "Nearline" or "Archive"');

			return Command::INVALID;
		}

		$this->config->setTerminalStorageClass($storageClass);
		$output->writeln('Terminal storage class set to ' . $storageClass);

		return Command::SUCCESS;
	}
}
