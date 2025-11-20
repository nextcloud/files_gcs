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

class AutoclassEnable extends Command {

	public function __construct(
		private Config $config
	) {
		parent::__construct();
	}

	protected function configure(): void {
		$this->setName('files_gcs:autoclass:enable')
			->setDescription('Enable autoclass for new buckets');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$this->config->setAutoclassEnabled(true);
		$output->writeln('Autoclass enabled');

		return Command::SUCCESS;
	}
}
