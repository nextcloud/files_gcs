<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
namespace OCA\FilesGCS\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Cloud\Storage\StorageClient;
use OCA\FilesGCS\Config;
use OCA\FilesGCS\Exceptions\BucketMissingException;
use Psr\Log\LoggerInterface;

class AutoclassService {
	public function __construct(
		private Config $config,
		private LoggerInterface $logger,
	) {
	}

	public function enable(string $bucketName, ?string $credentials = null): bool {
		if (!$credentials) {
			$credentials = $this->config->getCredentials();
		}

		$credentialsFetcher = new ServiceAccountCredentials(StorageClient::FULL_CONTROL_SCOPE, json_decode($credentials, true));
		$storage = new StorageClient(['credentialsFetcher' => $credentialsFetcher]);
		$bucket = $storage->bucket($bucketName);

		if (!$bucket->exists()) {
			throw new BucketMissingException();
		}

		try {
			$response = $bucket->update([
				'autoclass' => [
					'enabled' => true,
					'terminalStorageClass' => strtoupper($this->config->getTerminalStorageClass())
				]
			]);
		} catch (\Exception $e) {
			$this->logger->error('Failed to enable autoclass for bucket ' . $bucketName, ['exception' => $e]);
			return false;
		}

		if (empty($response['autoclass']['enabled'])) {
			return false;
		}

		return true;
	}
}
