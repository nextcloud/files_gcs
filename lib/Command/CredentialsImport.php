<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\FilesGCS\Command;

use OCA\FilesGCS\ConfigLexicon;
use OCP\AppFramework\Services\IAppConfig;
use OCP\Console\Attribute\Argument;
use OCP\Console\Attribute\AsCommand;
use OCP\Console\ExitCode;
use OCP\Console\IOutput;

#[AsCommand(
	name: 'files_gcs:credentials:import',
	description: 'Import Google service account credentials'
)]
class CredentialsImport {
	public function __construct(
		private IAppConfig $appConfig,
	) {
	}

	public function __invoke(
		IOutput $output,
		#[Argument]
		string $path,
	): ExitCode {
		if (!is_file($path)) {
			$output->writeln('Path provided doesn\'t exist');

			return ExitCode::Failure;
		}

		$credentials = file_get_contents($path);
		if ($credentials === false) {
			$output->writeln('Failed to import credentials');
			return ExitCode::Failure;
		}

		$this->appConfig->setAppValueString(ConfigLexicon::CREDENTIALS, $credentials);
		$output->writeln('Successfully imported credentials');

		return ExitCode::Success;
	}
}
