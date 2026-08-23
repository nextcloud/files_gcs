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
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Tester\CommandTester;
use Test\TestCase;

final class TerminalStorageClassSetTest extends TestCase {
	private IAppConfig&MockObject $appConfig;

	private TerminalStorageClassSet $command;
	private CommandTester $commandTester;

	public function setUp(): void {
		parent::setUp();

		$this->appConfig = $this->createMock(IAppConfig::class);

		$this->command = new TerminalStorageClassSet($this->appConfig);
		$this->commandTester = new CommandTester($this->command);
	}

	public function testSetsNearlineStorageClass(): void {
		$this->appConfig->expects($this->once())
			->method('setAppValueString')
			->with(ConfigLexicon::TERMINAL_STORAGE_CLASS, 'Nearline');

		$statusCode = $this->commandTester->execute(['storage' => 'Nearline']);
		$this->assertSame(Command::SUCCESS, $statusCode);
	}

	public function testAcceptsArchiveCaseInsensitivelyAndPreservesGivenCasing(): void {
		$this->appConfig->expects($this->once())
			->method('setAppValueString')
			->with(ConfigLexicon::TERMINAL_STORAGE_CLASS, 'ARCHIVE');

		$statusCode = $this->commandTester->execute(['storage' => 'ARCHIVE']);
		$this->assertSame(Command::SUCCESS, $statusCode);
	}

	public function testRejectsUnsupportedStorageClass(): void {
		$this->appConfig->expects($this->never())
			->method('setAppValueString');

		$statusCode = $this->commandTester->execute(['storage' => 'Coldline']);
		$this->assertSame(Command::INVALID, $statusCode);
	}

	public function testReportsFailureWhenNoStorageClassIsProvided(): void {
		$this->appConfig->expects($this->never())
			->method('setAppValueString');

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
