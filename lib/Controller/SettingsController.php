<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\FilesGCS\Controller;

use OCA\FilesGCS\AppInfo\Application;
use OCA\FilesGCS\Config;
use OCA\FilesGCS\Services\AutoclassService;
use OCA\FilesGCS\Services\BucketService;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\ApiRoute;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCSController;
use OCP\IRequest;

class SettingsController extends OCSController {

	public function __construct(
		IRequest $request,
		private Config $config,
		private AutoclassService $autoclassService,
		private BucketService $bucketService,
	) {
		parent::__construct(Application::APP_ID, $request);
	}

	/**
	 * Get the app config
	 *
	 * @return DataResponse<Http::STATUS_OK, array<string, mixed>, array{}>
	 *
	 * 200: Settings returned
	 */
	#[ApiRoute(verb: 'GET', url: '/config')]
	public function getConfig(): DataResponse {
		return new DataResponse([
			'autoclassEnabled' => $this->config->getAutoclassEnabled(),
			'terminalStorageClass' => $this->config->getTerminalStorageClass(),
			'credentialsExist' => !empty($this->config->getCredentials()),
			'buckets' => $this->bucketService->getBuckets(),
		]);
	}

	/**
	 * Set the app config
	 *
	 * @param bool $autoclassEnabled GCS autoclass parameter for buckets
	 * @param string $terminalStorageClass GCS terminal storage class for buckets
	 *
	 * @return DataResponse<Http::STATUS_OK, array<string, mixed>, array{}>
	 *
	 * 200: Settings returned
	 */
	#[ApiRoute(verb: 'PUT', url: '/config')]
	public function setConfig(bool $autoclassEnabled, string $terminalStorageClass): DataResponse {
		$this->config->setAutoclassEnabled($autoclassEnabled);
		$this->config->setTerminalStorageClass($terminalStorageClass);

		return new DataResponse([
			'success' => true,
			'autoclassEnabled' => $autoclassEnabled,
			'terminalStorageClass' => $terminalStorageClass
		]);
	}

	/**
	 * Set the GCS credentials
	 *
	 * @return DataResponse<Http::STATUS_OK, array<string, mixed>, array{}>
	 *
	 * 200: Saved credentials state returned
	 */
	#[ApiRoute(verb: 'POST', url: '/config/credentials')]
	public function setCredentials(): DataResponse {
		$credentials = $this->request->getUploadedFile('credentials');
		if (!isset($credentials['tmp_name'])) {
			return new DataResponse([
				'success' => true,
				'credentialsExist' => false,
			]);
		}

		if (!is_string($credentials['tmp_name'])) {
			return new DataResponse([
				'success' => true,
				'credentialsExist' => false,
			]);
		}

		$contents = file_get_contents($credentials['tmp_name']);
		$this->config->setCredentials($contents);

		return new DataResponse([
			'success' => true,
			'credentialsExist' => true
		]);
	}

}
