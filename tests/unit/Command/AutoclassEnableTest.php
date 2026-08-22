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
use OCA\FilesGCS\Service\BucketService;
use OCP\IConfig;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Test\TestCase;

final class AutoclassEnableTest extends TestCase {
	private Config&MockObject $appConfig;
	private IConfig&MockObject $config;
	private BucketService&MockObject $bucketService;

	private CommandTester $commandTester;

	public function setUp(): void {
		parent::setUp();

		$this->appConfig = $this->createMock(Config::class);
		$this->config = $this->createMock(IConfig::class);
		$this->bucketService = $this->createMock(BucketService::class);

		$command = new AutoclassEnable($this->appConfig, $this->config, $this->bucketService);
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
		$this->bucketService->expects($this->never())
			->method('setAutoclassForBucket');

		$statusCode = $this->commandTester->execute([]);
		$this->assertSame(Command::SUCCESS, $statusCode);
	}

	public function testReportsMissingObjectStoreConfiguration(): void {
		$this->appConfig->method('getAutoclassEnabled')->willReturn(true);
		$this->config->method('getSystemValue')
			->with('objectstore')
			->willReturn(['other-store' => []]);
		$this->bucketService->expects($this->never())
			->method('setAutoclassForBucket');

		$statusCode = $this->commandTester->execute(['--object-store' => 'gcs']);
		$this->assertSame(Command::SUCCESS, $statusCode);
	}

	public function testReturnsSuccessWithoutBucketOption(): void {
		$this->appConfig->method('getAutoclassEnabled')->willReturn(true);
		$this->config->method('getSystemValue')
			->with('objectstore')
			->willReturn(['gcs' => ['arguments' => ['hostname' => 'storage.googleapis.com']]]);
		$this->bucketService->expects($this->never())
			->method('setAutoclassForBucket');

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
		$this->bucketService->expects($this->never())
			->method('setAutoclassForBucket');

		$statusCode = $this->commandTester->execute(['--object-store' => 's3', '--bucket' => 'my-bucket']);
		$this->assertSame(Command::SUCCESS, $statusCode);
	}

	public function testReportsBucketNotYetCreated(): void {
		$this->appConfig->method('getAutoclassEnabled')->willReturn(true);
		$this->config->method('getSystemValue')
			->with('objectstore')
			->willReturn(['gcs' => ['arguments' => []]]);
		$this->appConfig->method('getCredentials')->willReturn('{"client_email":"a@b.com"}');
		$this->bucketService->expects($this->once())
			->method('setAutoclassForBucket')
			->with('my-bucket', true)
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
		$this->bucketService->expects($this->once())
			->method('setAutoclassForBucket')
			->willReturn(false);

		$statusCode = $this->commandTester->execute(['--object-store' => 'gcs', '--bucket' => 'my-bucket']);
		$this->assertSame(Command::FAILURE, $statusCode);
	}

	public function testReturnsSuccessWhenServiceEnablesAutoclass(): void {
		$this->appConfig->method('getAutoclassEnabled')->willReturn(true);
		$this->config->method('getSystemValue')
			->with('objectstore')
			->willReturn(['gcs' => ['arguments' => []]]);
		$this->appConfig->method('getCredentials')->willReturn('{"client_email":"a@b.com"}');
		$this->bucketService->expects($this->once())
			->method('setAutoclassForBucket')
			->willReturn(true);

		$statusCode = $this->commandTester->execute(['--object-store' => 'gcs', '--bucket' => 'my-bucket']);
		$this->assertSame(Command::SUCCESS, $statusCode);
	}
}
