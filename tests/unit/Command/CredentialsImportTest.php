<?php

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace OCA\FilesGCS\Tests\Command;

use OCA\FilesGCS\Command\CredentialsImport;
use OCA\FilesGCS\Config;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Tester\CommandTester;
use Test\TestCase;

final class CredentialsImportTest extends TestCase {
	private Config&MockObject $config;

	private CredentialsImport $command;
	private CommandTester $commandTester;

	public function setUp(): void {
		parent::setUp();

		$this->config = $this->createMock(Config::class);

		$this->command = new CredentialsImport($this->config);
		$this->commandTester = new CommandTester($this->command);
	}

	public function testReportsFailureWhenPathDoesNotExist(): void {
		$this->config->expects($this->never())
			->method('setCredentials');

		$statusCode = $this->commandTester->execute(['path' => '/nonexistent/path/credentials.json']);
		$this->assertSame(Command::FAILURE, $statusCode);
	}

	public function testReportsFailureWhenNoPathIsProvided(): void {
		$this->config->expects($this->never())
			->method('setCredentials');

		$input = $this->createMock(InputInterface::class);
		$input->method('getArgument')
			->with('path')
			->willReturn(null);
		$output = new BufferedOutput();

		$reflection = new \ReflectionMethod($this->command, 'execute');
		$reflection->setAccessible(true);
		$statusCode = $reflection->invoke($this->command, $input, $output);

		$this->assertSame(Command::FAILURE, $statusCode);
	}
}
