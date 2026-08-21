<?php

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace OCA\FilesGCS;

use OCP\IConfig;

/**
 * This roughly takes inspiration from `OC\Files\ObjectStore\PrimaryObjectStoreConfig`.
 * Since it is a private api, we build our own implementation here
 *
 */
class ObjectStoreConfig {
	public function __construct(
		private IConfig $config,
	) {
	}

	private function hasValidHostname(): bool {
		/** @var ?array $objectStore */
		$objectStore = $this->config->getSystemValue('objectstore', null);
		if ($objectStore !== null) {
			return isset($objectStore['arguments']['hostname'])
				   && strpos($objectStore['arguments']['hostname'], 'storage.googleapis.com') !== false;
		}

		/** @var ?array $objectStoreMultiBucket */
		$objectStoreMultiBucket = $this->config->getSystemValue('objectstore_multibucket', null);
		if ($objectStoreMultiBucket !== null) {
			return isset($objectStoreMultiBucket['arguments']['hostname'])
				   && strpos($objectStoreMultiBucket['arguments']['hostname'], 'storage.googleapis.com') !== false;
		}

		return false;
	}

	public function hasObjectStore(): bool {
		if (!$this->hasValidHostname()) {
			return false;
		}

		/** @var ?array $objectStore */
		$objectStore = $this->config->getSystemValue('objectstore', null);

		/** @var ?array $objectStoreMultiBucket */
		$objectStoreMultiBucket = $this->config->getSystemValue('objectstore_multibucket', null);

		return $objectStore !== null || $objectStoreMultiBucket !== null;
	}

	public function getBucketName(): ?string {
		if (!$this->hasValidHostname()) {
			return null;
		}

		/** @var ?array $objectStore */
		$objectStore = $this->config->getSystemValue('objectstore', null);
		if ($objectStore === null) {
			return null;
		}

		return $objectStore['arguments']['bucket'] ?? null;
	}

	public function getMultiBucketPrefix(): ?string {
		if (!$this->hasValidHostname()) {
			return null;
		}

		/** @var ?array $objectStore */
		$objectStore = $this->config->getSystemValue('objectstore', null);
		if ($objectStore !== null
			&& isset($objectStore['arguments']['multibucket'])
			&& $objectStore['arguments']['multibucket']) {
			return $objectStore['arguments']['bucket'] ?? null;
		}

		/** @var ?array $objectStoreMultiBucket */
		$objectStoreMultiBucket = $this->config->getSystemValue('objectstore_multibucket', null);
		if ($objectStoreMultiBucket === null) {
			return null;
		}

		return $objectStoreMultiBucket['arguments']['bucket'] ?? null;
	}

	public function hasMultipleObjectStorages(): bool {
		if (!$this->hasValidHostname()) {
			return false;
		}

		/** @var ?array $objectStore */
		$objectStore = $this->config->getSystemValue('objectstore', null);
		if ($objectStore !== null) {
			return $objectStore['arguments']['multibucket'] ?? false;
		}

		/** @var ?array $objectStoreMultiBucket */
		$objectStoreMultiBucket = $this->config->getSystemValue('objectstore_multibucket', null);
		return $objectStoreMultiBucket !== null;
	}
}
