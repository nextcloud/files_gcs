<?php

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace OCA\FilesGCS\Service;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Cloud\Storage\Bucket;
use Google\Cloud\Storage\StorageClient;
use OCA\FilesGCS\ConfigLexicon;
use OCA\FilesGCS\Exceptions\BucketMissingException;
use OCA\FilesGCS\ObjectStoreConfig;
use OCP\AppFramework\Services\IAppConfig;
use Psr\Log\LoggerInterface;

class BucketService {
	public function __construct(
		private ObjectStoreConfig $objectStoreConfig,
		private IAppConfig $appConfig,
		private LoggerInterface $logger,
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
				$bucketData[] = [
					'name' => $bucketName,
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
		$credentials = json_decode($this->appConfig->getAppValueString(ConfigLexicon::CREDENTIALS), true);
		$credentialsFetcher = new ServiceAccountCredentials(StorageClient::FULL_CONTROL_SCOPE, $credentials);
		return new StorageClient(['credentialsFetcher' => $credentialsFetcher]);
	}

	public function setAutoclassForBucket(string $bucketName, bool $enabled): bool {
		$client = $this->buildStorageClient();
		$bucket = $client->bucket($bucketName);

		if (!$bucket->exists()) {
			throw new BucketMissingException();
		}

		try {
			$response = $bucket->update([
				'autoclass' => [
					'enabled' => $enabled,
					'terminalStorageClass' => strtoupper($this->appConfig->getAppValueString(ConfigLexicon::TERMINAL_STORAGE_CLASS))
				]
			]);

			if (isset($response['autoclass']['enabled'])) {
				return $response['autoclass']['enabled'];
			}
		} catch (\Exception $e) {
			$this->logger->error('Failed to enable autoclass for bucket ' . $bucketName, ['exception' => $e]);
		}

		return false;
	}
}
