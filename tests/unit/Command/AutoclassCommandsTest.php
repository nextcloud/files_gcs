<?php

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace OCA\FilesGCS\Tests\Command;

use OCA\FilesGCS\Command\AutoclassCommands;
use OCA\FilesGCS\ConfigLexicon;
use OCA\FilesGCS\Exceptions\BucketMissingException;
use OCA\FilesGCS\Service\BucketService;
use OCP\AppFramework\Services\IAppConfig;
use OCP\Console\ExitCode;
use OCP\Console\IOutput;
use OCP\IConfig;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

final class AutoclassCommandsTest extends TestCase {
	private IAppConfig&MockObject $appConfig;
	private IConfig&MockObject $config;
	private BucketService&MockObject $bucketService;
	private IOutput&MockObject $output;

	private AutoclassCommands $command;

	public function setUp(): void {
		parent::setUp();

		$this->appConfig = $this->createMock(IAppConfig::class);
		$this->config = $this->createMock(IConfig::class);
		$this->bucketService = $this->createMock(BucketService::class);
		$this->output = $this->createMock(IOutput::class);

		$this->command = new AutoclassCommands($this->appConfig, $this->config, $this->bucketService);
	}

	public function testEnablesAutoclassWhenCurrentlyDisabled(): void {
		$this->appConfig->method('getAppValueBool')->with(ConfigLexicon::AUTOCLASS_ENABLED)->willReturn(false);
		$this->appConfig->expects($this->once())
			->method('setAppValueBool')
			->with(ConfigLexicon::AUTOCLASS_ENABLED, true);

		$statusCode = $this->command->enable($this->output);
		$this->assertSame(ExitCode::Success, $statusCode);
	}

	public function testDoesNotReenableAutoclassWhenAlreadyEnabled(): void {
		$this->appConfig->method('getAppValueBool')->with(ConfigLexicon::AUTOCLASS_ENABLED)->willReturn(true);
		$this->appConfig->expects($this->never())
			->method('setAppValueBool');

		$statusCode = $this->command->enable($this->output);
		$this->assertSame(ExitCode::Success, $statusCode);
	}

	public function testReturnsSuccessWithoutObjectStoreOption(): void {
		$this->appConfig->method('getappValueBool')->with(ConfigLexicon::AUTOCLASS_ENABLED)->willReturn(true);
		$this->config->expects($this->never())->method('getSystemValue');
		$this->bucketService->expects($this->never())->method('setAutoclassForBucket');

		$statusCode = $this->command->enable($this->output);
		$this->assertSame(ExitCode::Success, $statusCode);
	}

	public function testReportsMissingObjectStoreConfiguration(): void {
		$this->appConfig->method('getAppValueBool')->with(ConfigLexicon::AUTOCLASS_ENABLED)->willReturn(true);
		$this->config->method('getSystemValue')
			->with('objectstore')
			->willReturn(['other-store' => []]);
		$this->bucketService->expects($this->never())
			->method('setAutoclassForBucket');

		$statusCode = $this->command->enable($this->output, 'gcs');
		$this->assertSame(ExitCode::Failure, $statusCode);
	}

	public function testReturnsSuccessWithoutBucketOption(): void {
		$this->appConfig->method('getAppValueBool')->with(ConfigLexicon::AUTOCLASS_ENABLED)->willReturn(true);
		$this->config->method('getSystemValue')
			->with('objectstore')
			->willReturn(['gcs' => ['arguments' => ['hostname' => 'storage.googleapis.com']]]);
		$this->bucketService->expects($this->never())->method('setAutoclassForBucket');

		$statusCode = $this->command->enable($this->output, 'gcs');
		$this->assertSame(ExitCode::Failure, $statusCode);
	}

	public function testSkipsSilentlyForNonGcsHostname(): void {
		$this->appConfig->method('getAppValueBool')->with(ConfigLexicon::AUTOCLASS_ENABLED)->willReturn(true);
		$this->config->method('getSystemValue')
			->with('objectstore')
			->willReturn(['s3' => ['arguments' => ['hostname' => 's3.amazonaws.com']]]);
		$this->appConfig->expects($this->never())->method('getAppValueString');
		$this->bucketService->expects($this->never())->method('setAutoclassForBucket');

		$statusCode = $this->command->enable($this->output, 's3', 'my-bucket');
		$this->assertSame(ExitCode::Failure, $statusCode);
	}

	public function testReportsBucketNotYetCreated(): void {
		$this->appConfig->method('getAppValueBool')->with(ConfigLexicon::AUTOCLASS_ENABLED)->willReturn(true);
		$this->config->method('getSystemValue')
			->with('objectstore')
			->willReturn(['gcs' => ['arguments' => []]]);
		$this->appConfig->method('getAppValueString')
			->with(ConfigLexicon::TERMINAL_STORAGE_CLASS)
			->willReturn('{"client_email":"a@b.com"}');
		$this->bucketService->expects($this->once())
			->method('setAutoclassForBucket')
			->with('my-bucket', true)
			->willThrowException(new BucketMissingException());

		$statusCode = $this->command->enable($this->output, 'gcs', 'my-bucket');
		$this->assertSame(ExitCode::Failure, $statusCode);
	}

	public function testReturnsFailureWhenServiceFailsToEnableAutoclass(): void {
		$this->appConfig->method('getAppValueBool')->with(ConfigLexicon::AUTOCLASS_ENABLED)->willReturn(true);
		$this->config->method('getSystemValue')
			->with('objectstore')
			->willReturn(['gcs' => ['arguments' => []]]);
		$this->appConfig->method('getAppValueString')
			->with(ConfigLexicon::CREDENTIALS)
			->willReturn('{"client_email":"a@b.com"}');
		$this->bucketService->expects($this->once())
			->method('setAutoclassForBucket')
			->willReturn(false);

		$statusCode = $this->command->enable($this->output, 'gcs', 'my-bucket');
		$this->assertSame(ExitCode::Failure, $statusCode);
	}

	public function testReturnsSuccessWhenServiceEnablesAutoclass(): void {
		$this->appConfig->method('getAppValueBool')->with(ConfigLexicon::AUTOCLASS_ENABLED)->willReturn(true);
		$this->config->method('getSystemValue')
			->with('objectstore')
			->willReturn(['gcs' => ['arguments' => []]]);
		$this->appConfig->method('getAppValueString')
			->with(ConfigLexicon::CREDENTIALS)
			->willReturn('{"client_email":"a@b.com"}');
		$this->bucketService->expects($this->once())->method('setAutoclassForBucket')->willReturn(true);

		$statusCode = $this->command->enable($this->output, 'gcs', 'my-bucket');
		$this->assertSame(ExitCode::Success, $statusCode);
	}

	public function testDisable(): void {
		$this->appConfig->expects($this->once())
			->method('setAppValueBool')
			->with(ConfigLexicon::AUTOCLASS_ENABLED, false);

		$statusCode = $this->command->disable($this->output);
		$this->assertSame(ExitCode::Success, $statusCode);
	}
}
