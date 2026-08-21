<?php

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace OCA\FilesGCS\Tests\Command;

use OCA\FilesGCS\Command\AutoclassDisable;
use OCA\FilesGCS\Config;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Test\TestCase;

final class AutoclassDisableTest extends TestCase {
	private Config&MockObject $config;

	private CommandTester $commandTester;

	public function setUp(): void {
		parent::setUp();

		$this->config = $this->createMock(Config::class);

		$command = new AutoclassDisable($this->config);
		$this->commandTester = new CommandTester($command);
	}

	public function testDisablesAutoclass(): void {
		$this->config->expects($this->once())
			->method('setAutoclassEnabled')
			->with(false);

		$statusCode = $this->commandTester->execute([]);
		$this->assertSame(Command::SUCCESS, $statusCode);
	}
}
