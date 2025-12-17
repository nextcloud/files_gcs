<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\FilesGCS\Controller;

use OCA\FilesGCS\AppInfo\Application;
use OCA\FilesGCS\Config;
use OCP\AppFramework\Http\Attribute\FrontpageRoute;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCSController;
use OCP\IRequest;

class SettingsController extends OCSController {

	public function __construct(
		IRequest $request,
		private Config $config,
	) {
		parent::__construct(Application::APP_ID, $request);
	}

	#[FrontpageRoute(verb: 'GET', url: '/config')]
	public function getConfig(): DataResponse {
		$autoclassEnabled = $this->config->getAutoclassEnabled();
		$terminalStorageClass = $this->config->getTerminalStorageClass();
		$credentialsExist = !empty($this->config->getCredentials());

		return new DataResponse([
			'autoclassEnabled' => $autoclassEnabled,
			'terminalStorageClass' => $terminalStorageClass,
			'credentialsExist' => $credentialsExist
		]);
	}

	#[FrontpageRoute(verb: 'PUT', url: '/config')]
	public function setConfig(bool $autoclassEnabled, string $terminalStorageClass): DataResponse {
		$this->config->setAutoclassEnabled($autoclassEnabled);
		$this->config->setTerminalStorageClass($terminalStorageClass);

		return new DataResponse([
			'success' => true,
			'autoclassEnabled' => $autoclassEnabled,
			'terminalStorageClass' => $terminalStorageClass
		]);
	}

	#[FrontpageRoute(verb: 'POST', url: '/config/credentials')]
	public function setCredentials(): DataResponse {
		$credentials = $this->request->getUploadedFile('credentials');
		$contents = file_get_contents($credentials['tmp_name']);
		$this->config->setCredentials($contents);

		return new DataResponse([
			'success' => true,
			'credentialsExist' => true
		]);
	}

}
