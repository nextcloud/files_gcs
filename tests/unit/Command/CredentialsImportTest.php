<?php

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace OCA\FilesGCS\Tests\Command;

use OCA\FilesGCS\Command\CredentialsImport;
use OCP\AppFramework\Services\IAppConfig;
use OCP\Console\ExitCode;
use OCP\Console\IOutput;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

final class CredentialsImportTest extends TestCase {
	private IAppConfig&MockObject $config;
	private IOutput&MockObject $output;

	private CredentialsImport $command;

	public function setUp(): void {
		parent::setUp();

		$this->config = $this->createMock(IAppConfig::class);
		$this->command = new CredentialsImport($this->config);

		$this->output = $this->createMock(IOutput::class);
	}

	public function testReportsFailureWhenPathDoesNotExist(): void {
		$this->config->expects($this->never())
			->method('setAppValueString');

		$statusCode = $this->command->__invoke($this->output, '/nonexistent/path/credentials.json');
		$this->assertSame(ExitCode::Failure, $statusCode);
	}
}
