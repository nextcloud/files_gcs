<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\FilesGCS;

use OCP\AppFramework\Services\IAppConfig;

class Config {
	public function __construct(
		private IAppConfig $appConfig,
	) {
	}

	public function setAutoclassEnabled(bool $enabled): void {
		$this->appConfig->setAppValueBool('autoclass_enabled', $enabled);
	}

	public function setTerminalStorageClass(string $storageClass): void {
		$this->appConfig->setAppValueString('terminal_storage_class', $storageClass);
	}

	public function setCredentials(string $credentials): void {
		$this->appConfig->setAppValueString('credentials', $credentials, sensitive: true);
	}

	public function getAutoclassEnabled(): bool {
		return $this->appConfig->getAppValueBool('autoclass_enabled', false);
	}

	public function getTerminalStorageClass(): string {
		return $this->appConfig->getAppValueString('terminal_storage_class', 'Nearline');
	}

	public function getCredentials(): string {
		return $this->appConfig->getAppValueString('credentials', '');
	}
}
