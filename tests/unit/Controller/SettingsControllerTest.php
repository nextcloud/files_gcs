<?php

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace OCA\FilesGCS\Tests\Controller;

use OCA\FilesGCS\ConfigLexicon;
use OCA\FilesGCS\Controller\SettingsController;
use OCA\FilesGCS\Service\BucketService;
use OCP\AppFramework\Http;
use OCP\AppFramework\Services\IAppConfig;
use OCP\IRequest;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

final class SettingsControllerTest extends TestCase {
	private IRequest&MockObject $request;
	private IAppConfig&MockObject $appConfig;
	private BucketService&MockObject $bucketService;

	private SettingsController $controller;

	public function setUp(): void {
		parent::setUp();

		$this->request = $this->createMock(IRequest::class);
		$this->appConfig = $this->createMock(IAppConfig::class);
		$this->bucketService = $this->createMock(BucketService::class);

		$this->controller = new SettingsController(
			$this->request,
			$this->appConfig,
			$this->bucketService,
		);
	}

	public function testGetConfigReflectsCurrentState(): void {
		$this->appConfig->method('getAppValueBool')->with(ConfigLexicon::AUTOCLASS_ENABLED)->willReturn(true);
		$this->appConfig->expects($this->exactly(2))
			->method('getAppValueString')
			->willReturnCallback(fn (string $config) => match ($config) {
				ConfigLexicon::TERMINAL_STORAGE_CLASS => 'Archive',
				ConfigLexicon::CREDENTIALS => '{"client_email":"a@b.com"}',
				default => '',
			});
		$this->bucketService->method('getBuckets')->willReturn(['my-bucket' => ['autoclass' => ['enabled' => true]]]);

		$response = $this->controller->getConfig();

		$this->assertSame(Http::STATUS_OK, $response->getStatus());
		$this->assertSame([
			'autoclassEnabled' => true,
			'terminalStorageClass' => 'Archive',
			'credentialsExist' => true,
			'buckets' => ['my-bucket' => ['autoclass' => ['enabled' => true]]],
		], $response->getData());
	}

	public function testGetConfigReportsNoCredentialsWhenEmpty(): void {
		$this->appConfig->method('getAppValueBool')->with(ConfigLexicon::AUTOCLASS_ENABLED)->willReturn(false);
		$this->appConfig->expects($this->exactly(2))
			->method('getAppValueString')
			->willReturnCallback(fn (string $config) => match ($config) {
				ConfigLexicon::TERMINAL_STORAGE_CLASS => 'Nearline',
				ConfigLexicon::CREDENTIALS => '',
				default => '',
			});
		$this->bucketService->method('getBuckets')->willReturn([]);

		$response = $this->controller->getConfig();

		$this->assertFalse($response->getData()['credentialsExist']);
	}

	public function testSetConfigPersistsAndReturnsValues(): void {
		$this->appConfig->expects($this->once())
			->method('setAppValueBool')
			->with(ConfigLexicon::AUTOCLASS_ENABLED, true);
		$this->appConfig->expects($this->once())
			->method('setAppValueString')
			->with(ConfigLexicon::TERMINAL_STORAGE_CLASS, 'Archive');

		$response = $this->controller->setConfig(true, 'Archive');

		$this->assertSame(Http::STATUS_OK, $response->getStatus());
		$this->assertSame([
			'success' => true,
			'autoclassEnabled' => true,
			'terminalStorageClass' => 'Archive',
		], $response->getData());
	}

	public function testSetCredentialsWithoutUploadedFileReportsFailure(): void {
		$this->request->method('getUploadedFile')
			->with('credentials')
			->willReturn(null);
		$this->appConfig->expects($this->never())
			->method('setAppValueString');

		$response = $this->controller->setCredentials();

		$this->assertSame([
			'success' => true,
			'credentialsExist' => false,
		], $response->getData());
	}

	public function testSetCredentialsWithNonStringTmpNameReportsFailure(): void {
		$this->request->method('getUploadedFile')
			->with('credentials')
			->willReturn(['tmp_name' => ['unexpected', 'array']]);
		$this->appConfig->expects($this->never())
			->method('setAppValueString');

		$response = $this->controller->setCredentials();

		$this->assertSame([
			'success' => true,
			'credentialsExist' => false,
		], $response->getData());
	}

	public function testSetAutoclassForBucket(): void {
		$this->bucketService->expects($this->once())
			->method('setAutoclassForBucket')
			->with('my-bucket', true)
			->willReturn(true);

		$response = $this->controller->setAutoclassForBucket('my-bucket', true);
		$this->assertSame([ 'autoclass' => true ], $response->getData());
	}

	public function testSetAutoclassForBucketWithEmptyNameFails(): void {
		$this->bucketService->expects($this->once())
			->method('setAutoclassForBucket')
			->with('', true)
			->willReturn(false);

		$response = $this->controller->setAutoclassForBucket('', true);
		$this->assertSame([ 'autoclass' => false], $response->getData());
	}
}
