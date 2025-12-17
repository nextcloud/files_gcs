<?php

declare (strict_types=1);
/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\FilesGCS\Settings;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\Settings\ISettings;
use OCP\Util;

class Admin implements ISettings {
	public function __construct() {
	}

	public function getForm(): TemplateResponse {
		Util::addScript('files_gcs', 'files_gcs-settings');
		return new TemplateResponse('files_gcs', 'settings/admin');
	}

	public function getSection(): string {
		return 'files_gcs';
	}

	public function getPriority(): int {
		return 0;
	}
}
