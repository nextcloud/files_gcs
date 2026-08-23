<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\FilesGCS\Command;

use OCA\FilesGCS\ConfigLexicon;
use OCP\AppFramework\Services\IAppConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AutoclassDisable extends Command {

	public function __construct(
		private IAppConfig $appConfig,
	) {
		parent::__construct();
	}

	protected function configure(): void {
		$this->setName('files_gcs:autoclass:disable')
			->setDescription('Disable autoclass for new buckets');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$this->appConfig->setAppValueBool(ConfigLexicon::AUTOCLASS_ENABLED, false);
		$output->writeln('Autoclass disabled');

		return Command::SUCCESS;
	}
}
