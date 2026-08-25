<?php

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace OCA\FilesGCS\Tests\Command;

use OCA\FilesGCS\Command\TerminalStorageClassSet;
use OCA\FilesGCS\ConfigLexicon;
use OCP\AppFramework\Services\IAppConfig;
use OCP\Console\ExitCode;
use OCP\Console\IOutput;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

final class TerminalStorageClassSetTest extends TestCase {
	private IAppConfig&MockObject $appConfig;
	private IOutput&MockObject $output;
	private TerminalStorageClassSet $command;

	public function setUp(): void {
		parent::setUp();

		$this->appConfig = $this->createMock(IAppConfig::class);
		$this->command = new TerminalStorageClassSet($this->appConfig);

		$this->output = $this->createMock(IOutput::class);
	}

	public function testSetsNearlineStorageClass(): void {
		$this->appConfig->expects($this->once())
			->method('setAppValueString')
			->with(ConfigLexicon::TERMINAL_STORAGE_CLASS, 'Nearline');

		$statusCode = $this->command->__invoke($this->output, 'Nearline');
		$this->assertSame(ExitCode::Success, $statusCode);
	}

	public function testAcceptsArchiveCaseInsensitivelyAndPreservesGivenCasing(): void {
		$this->appConfig->expects($this->once())
			->method('setAppValueString')
			->with(ConfigLexicon::TERMINAL_STORAGE_CLASS, 'ARCHIVE');

		$statusCode = $this->command->__invoke($this->output, 'ARCHIVE');
		$this->assertSame(ExitCode::Success, $statusCode);
	}

	public function testRejectsUnsupportedStorageClass(): void {
		$this->appConfig->expects($this->never())
			->method('setAppValueString');

		$statusCode = $this->command->__invoke($this->output, 'Coldline');
		$this->assertSame(ExitCode::Invalid, $statusCode);
	}
}
