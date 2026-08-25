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
	name: 'files_gcs:terminal_storage_class:set',
	description: 'Set the terminal storage class for new buckets. Possible values are "Nearline" (default) and "Archive"'
)]
class TerminalStorageClassSet {
	public function __construct(
		private IAppConfig $appConfig,
	) {
	}

	public function __invoke(
		IOutput $output,
		#[Argument]
		string $storage,
	): ExitCode {
		if (strtolower($storage) !== 'nearline' && strtolower($storage) !== 'archive') {
			$output->writeln('Invalid storage class. Terminal storage class must be "Nearline" or "Archive"');

			return ExitCode::Invalid;
		}

		$this->appConfig->setAppValueString(ConfigLexicon::TERMINAL_STORAGE_CLASS, $storage);
		$output->writeln('Terminal storage class set to ' . $storage);

		return ExitCode::Success;
	}
}
