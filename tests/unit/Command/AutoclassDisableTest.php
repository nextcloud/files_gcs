<?php

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace OCA\FilesGCS\Tests\Command;

use OCA\FilesGCS\Command\AutoclassDisable;
use OCA\FilesGCS\ConfigLexicon;
use OCP\AppFramework\Services\IAppConfig;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Test\TestCase;

final class AutoclassDisableTest extends TestCase {
	private IAppConfig&MockObject $appConfig;

	private CommandTester $commandTester;

	public function setUp(): void {
		parent::setUp();

		$this->appConfig = $this->createMock(IAppConfig::class);

		$command = new AutoclassDisable($this->appConfig);
		$this->commandTester = new CommandTester($command);
	}

	public function testDisablesAutoclass(): void {
		$this->appConfig->expects($this->once())
			->method('setAppValueBool')
			->with(ConfigLexicon::AUTOCLASS_ENABLED, false);

		$statusCode = $this->commandTester->execute([]);
		$this->assertSame(Command::SUCCESS, $statusCode);
	}
}
