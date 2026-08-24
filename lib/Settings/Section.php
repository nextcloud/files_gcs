<?php

declare (strict_types=1);
/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\FilesGCS\Settings;

use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Settings\IIconSection;

class Section implements IIconSection {
	public function __construct(
		private IL10N $l10n,
		private IURLGenerator $url,
	) {
	}

	#[\Override]
	public function getID(): string {
		return 'files_gcs';
	}

	#[\Override]
	public function getName(): string {
		return $this->l10n->t('Google Cloud Storage');
	}

	#[\Override]
	public function getPriority(): int {
		return 50;
	}

	#[\Override]
	public function getIcon(): string {
		return $this->url->imagePath('files_gcs', 'app-dark.svg');
	}
}
