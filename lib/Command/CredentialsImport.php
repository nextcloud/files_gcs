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

class CredentialsImport extends Command {
	public function __construct(
		private Config $config,
	) {
		parent::__construct();
	}

	protected function configure(): void {
		$this->setName('files_gcs:credentials:import')
			->setDescription('Import Google service account credentials')
			->addArgument('path', InputArgument::REQUIRED, 'Path to the credentials file');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$path = $input->getArgument('path');

		if (!is_file($path)) {
			$output->writeln('Path provided doesn\'t exist');

			return Command::FAILURE;
		}

		$credentials = file_get_contents($path);
		$this->config->setCredentials($credentials);

		$output->writeln('Successfully imported credentials');

		return Command::SUCCESS;
	}
}
