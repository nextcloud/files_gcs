<?php

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace OCA\FilesGCS\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Cloud\Storage\Bucket;
use Google\Cloud\Storage\StorageClient;
use OCA\FilesGCS\Config;
use OCA\FilesGCS\ObjectStoreConfig;

class BucketService {
	public function __construct(
		private ObjectStoreConfig $objectStoreConfig,
		private Config $config,
	) {
	}

	public function getBuckets(): array {
		if (!$this->objectStoreConfig->hasObjectStore()) {
			return [];
		}

		$name = $this->objectStoreConfig->getBucketName();
		$prefix = $this->objectStoreConfig->hasMultipleObjectStorages()
			? $this->objectStoreConfig->getMultiBucketPrefix()
			: null;

		return $this->fetchBuckets($name, $prefix);
	}

	private function fetchBuckets(?string $name, ?string $prefix): array {
		$bucketData = [];
		if ($name === null && $prefix === null) {
			return $bucketData;
		}

		$storageClient = $this->buildStorageClient();
		$pageToken = null;
		while (true) {
			$options = [];

			if ($pageToken !== null) {
				$options['pageToken'] = $pageToken;
			}

			$buckets = $storageClient->buckets($options);

			/** @var Bucket $bucket */
			foreach ($buckets as $bucket) {
				$bucketName = $bucket->name();
				if ($bucketName !== $name && strpos($bucketName, $prefix) !== 0) {
					continue;
				}

				$bucketInfo = $bucket->info();
				$bucketData[$bucketName] = [
					'autoclass' => isset($bucketInfo['autoclass'])
						? $bucketInfo['autoclass']
						: [
							'enabled' => false,
						],
				];
			}

			$pageToken = $buckets->nextResultToken();

			if ($pageToken === null) {
				break;
			}
		}

		return $bucketData;

	}

	private function buildStorageClient(): StorageClient {
		/** @var array<array-key, mixed> $credentials */
		$credentials = json_decode($this->config->getCredentials(), true);
		$credentialsFetcher = new ServiceAccountCredentials(StorageClient::FULL_CONTROL_SCOPE, $credentials);
		return new StorageClient(['credentialsFetcher' => $credentialsFetcher]);
	}
}
