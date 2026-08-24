<?php

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace OCA\FilesGCS;

use OCP\Config\Lexicon\Entry;
use OCP\Config\Lexicon\ILexicon;
use OCP\Config\Lexicon\Strictness;
use OCP\Config\ValueType;

class ConfigLexicon implements ILexicon {
	public const string AUTOCLASS_ENABLED = 'autoclass_enabled';
	public const string TERMINAL_STORAGE_CLASS = 'terminal_storage_class';
	public const string CREDENTIALS = 'credentials';

	#[\Override]
	public function getStrictness(): Strictness {
		return Strictness::EXCEPTION;
	}

	#[\Override]
	public function getAppConfigs(): array {
		return [
			new Entry(
				self::AUTOCLASS_ENABLED,
				ValueType::BOOL,
				false,
				'Whether the autoclass setting is enabled on Google Cloud Storage',
			),
			new Entry(
				self::TERMINAL_STORAGE_CLASS,
				ValueType::STRING,
				'Nearline',
				'The storage class to be set when autoclass is enabled. Valid values are \'Nearline\' and \'Archive\''
			),
			new Entry(
				self::CREDENTIALS,
				ValueType::STRING
			),
		];
	}

	#[\Override]
	public function getUserConfigs(): array {
		return [];
	}
}
