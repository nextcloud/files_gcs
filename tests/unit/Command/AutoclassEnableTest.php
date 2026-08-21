<?php

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace OCA\FilesGCS\Tests\Command;

use OCA\FilesGCS\Command\AutoclassEnable;
use OCA\FilesGCS\Config;
use OCA\FilesGCS\Exceptions\BucketMissingException;
use OCA\FilesGCS\Service\AutoclassService;
use OCP\IConfig;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Test\TestCase;

final class AutoclassEnableTest extends TestCase {
	private Config&MockObject $appConfig;
	private IConfig&MockObject $config;
	private AutoclassService&MockObject $service;

	private CommandTester $commandTester;

	public function setUp(): void {
		parent::setUp();

		$this->appConfig = $this->createMock(Config::class);
		$this->config = $this->createMock(IConfig::class);
		$this->service = $this->createMock(AutoclassService::class);

		$command = new AutoclassEnable($this->appConfig, $this->config, $this->service);
		$this->commandTester = new CommandTester($command);
	}

	public function testEnablesAutoclassWhenCurrentlyDisabled(): void {
		$this->appConfig->method('getAutoclassEnabled')->willReturn(false);
		$this->appConfig->expects($this->once())
			->method('setAutoclassEnabled')
			->with(true);

		$statusCode = $this->commandTester->execute([]);
		$this->assertSame(Command::SUCCESS, $statusCode);
	}

	public function testDoesNotReenableAutoclassWhenAlreadyEnabled(): void {
		$this->appConfig->method('getAutoclassEnabled')->willReturn(true);
		$this->appConfig->expects($this->never())
			->method('setAutoclassEnabled');

		$statusCode = $this->commandTester->execute([]);
		$this->assertSame(Command::SUCCESS, $statusCode);
	}

	public function testReturnsSuccessWithoutObjectStoreOption(): void {
		$this->appConfig->method('getAutoclassEnabled')->willReturn(true);
		$this->config->expects($this->never())
			->method('getSystemValue');
		$this->service->expects($this->never())
			->method('enable');

		$statusCode = $this->commandTester->execute([]);
		$this->assertSame(Command::SUCCESS, $statusCode);
	}

	public function testReportsMissingObjectStoreConfiguration(): void {
		$this->appConfig->method('getAutoclassEnabled')->willReturn(true);
		$this->config->method('getSystemValue')
			->with('objectstore')
			->willReturn(['other-store' => []]);
		$this->service->expects($this->never())
			->method('enable');

		$statusCode = $this->commandTester->execute(['--object-store' => 'gcs']);
		$this->assertSame(Command::SUCCESS, $statusCode);
	}

	public function testReturnsSuccessWithoutBucketOption(): void {
		$this->appConfig->method('getAutoclassEnabled')->willReturn(true);
		$this->config->method('getSystemValue')
			->with('objectstore')
			->willReturn(['gcs' => ['arguments' => ['hostname' => 'storage.googleapis.com']]]);
		$this->service->expects($this->never())
			->method('enable');

		$statusCode = $this->commandTester->execute(['--object-store' => 'gcs']);
		$this->assertSame(Command::SUCCESS, $statusCode);
	}

	public function testSkipsSilentlyForNonGcsHostname(): void {
		$this->appConfig->method('getAutoclassEnabled')->willReturn(true);
		$this->config->method('getSystemValue')
			->with('objectstore')
			->willReturn(['s3' => ['arguments' => ['hostname' => 's3.amazonaws.com']]]);
		$this->appConfig->expects($this->never())
			->method('getCredentials');
		$this->service->expects($this->never())
			->method('enable');

		$statusCode = $this->commandTester->execute(['--object-store' => 's3', '--bucket' => 'my-bucket']);
		$this->assertSame(Command::SUCCESS, $statusCode);
	}

	public function testReportsMissingCredentials(): void {
		$this->appConfig->method('getAutoclassEnabled')->willReturn(true);
		$this->config->method('getSystemValue')
			->with('objectstore')
			->willReturn(['gcs' => ['arguments' => ['hostname' => 'storage.googleapis.com']]]);
		$this->appConfig->method('getCredentials')->willReturn('');
		$this->service->expects($this->never())
			->method('enable');

		$statusCode = $this->commandTester->execute(['--object-store' => 'gcs', '--bucket' => 'my-bucket']);
		$this->assertSame(Command::SUCCESS, $statusCode);
	}

	public function testReportsBucketNotYetCreated(): void {
		$this->appConfig->method('getAutoclassEnabled')->willReturn(true);
		$this->config->method('getSystemValue')
			->with('objectstore')
			->willReturn(['gcs' => ['arguments' => []]]);
		$this->appConfig->method('getCredentials')->willReturn('{"client_email":"a@b.com"}');
		$this->service->expects($this->once())
			->method('enable')
			->with('my-bucket', '{"client_email":"a@b.com"}')
			->willThrowException(new BucketMissingException());

		$statusCode = $this->commandTester->execute(['--object-store' => 'gcs', '--bucket' => 'my-bucket']);
		$this->assertSame(Command::SUCCESS, $statusCode);
	}

	public function testReturnsFailureWhenServiceFailsToEnableAutoclass(): void {
		$this->appConfig->method('getAutoclassEnabled')->willReturn(true);
		$this->config->method('getSystemValue')
			->with('objectstore')
			->willReturn(['gcs' => ['arguments' => []]]);
		$this->appConfig->method('getCredentials')->willReturn('{"client_email":"a@b.com"}');
		$this->service->expects($this->once())
			->method('enable')
			->willReturn(false);

		$statusCode = $this->commandTester->execute(['--object-store' => 'gcs', '--bucket' => 'my-bucket']);

		$this->assertSame(Command::FAILURE, $statusCode);
		$this->assertStringContainsString('Failed to enable autoclass for bucket my-bucket', $this->commandTester->getDisplay());
	}

	public function testReturnsSuccessWhenServiceEnablesAutoclass(): void {
		$this->appConfig->method('getAutoclassEnabled')->willReturn(true);
		$this->config->method('getSystemValue')
			->with('objectstore')
			->willReturn(['gcs' => ['arguments' => []]]);
		$this->appConfig->method('getCredentials')->willReturn('{"client_email":"a@b.com"}');
		$this->service->expects($this->once())
			->method('enable')
			->willReturn(true);

		$statusCode = $this->commandTester->execute(['--object-store' => 'gcs', '--bucket' => 'my-bucket']);
		$this->assertSame(Command::SUCCESS, $statusCode);
	}
}
