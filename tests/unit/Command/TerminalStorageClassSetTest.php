<?php

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace OCA\FilesGCS\Tests\Command;

use OCA\FilesGCS\Command\TerminalStorageClassSet;
use OCA\FilesGCS\Config;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Tester\CommandTester;
use Test\TestCase;

final class TerminalStorageClassSetTest extends TestCase {
	private Config&MockObject $config;

	private TerminalStorageClassSet $command;
	private CommandTester $commandTester;

	public function setUp(): void {
		parent::setUp();

		$this->config = $this->createMock(Config::class);

		$this->command = new TerminalStorageClassSet($this->config);
		$this->commandTester = new CommandTester($this->command);
	}

	public function testSetsNearlineStorageClass(): void {
		$this->config->expects($this->once())
			->method('setTerminalStorageClass')
			->with('Nearline');

		$statusCode = $this->commandTester->execute(['storage' => 'Nearline']);
		$this->assertSame(Command::SUCCESS, $statusCode);
	}

	public function testAcceptsArchiveCaseInsensitivelyAndPreservesGivenCasing(): void {
		$this->config->expects($this->once())
			->method('setTerminalStorageClass')
			->with('ARCHIVE');

		$statusCode = $this->commandTester->execute(['storage' => 'ARCHIVE']);
		$this->assertSame(Command::SUCCESS, $statusCode);
	}

	public function testRejectsUnsupportedStorageClass(): void {
		$this->config->expects($this->never())
			->method('setTerminalStorageClass');

		$statusCode = $this->commandTester->execute(['storage' => 'Coldline']);
		$this->assertSame(Command::INVALID, $statusCode);
	}

	public function testReportsFailureWhenNoStorageClassIsProvided(): void {
		$this->config->expects($this->never())
			->method('setTerminalStorageClass');

		$input = $this->createMock(InputInterface::class);
		$input->method('getArgument')
			->with('storage')
			->willReturn(null);
		$output = new BufferedOutput();

		$reflection = new \ReflectionMethod($this->command, 'execute');
		$reflection->setAccessible(true);
		$statusCode = $reflection->invoke($this->command, $input, $output);

		$this->assertSame(Command::INVALID, $statusCode);
	}
}
