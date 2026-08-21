<?php

/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace OCA\FilesGCS\Tests\Service;

use OCA\FilesGCS\Config;
use OCA\FilesGCS\ObjectStoreConfig;
use OCA\FilesGCS\Service\BucketService;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

final class BucketServiceTest extends TestCase {
	private ObjectStoreConfig&MockObject $objectStoreConfig;
	private Config&MockObject $config;

	private BucketService $service;

	public function setUp(): void {
		parent::setUp();

		$this->objectStoreConfig = $this->createMock(ObjectSToreConfig::class);
		$this->config = $this->createMock(Config::class);
		$this->service = new BucketService(
			$this->objectStoreConfig,
			$this->config,
		);
	}

	public function tearDown(): void {
		parent::tearDown();
	}

	public function testGetBucketsWithoutNameOrPrefix(): void {
		$this->objectStoreConfig->expects($this->once())
			->method('hasObjectStore')
			->willReturn(true);
		$this->objectStoreConfig->expects($this->once())
			->method('getBucketName')
			->willReturn(null);
		$this->objectStoreConfig->expects($this->once())
			->method('hasMultipleObjectStorages')
			->willReturn(true);
		$this->objectStoreConfig->expects($this->once())
			->method('getMultiBucketPrefix')
			->willReturn(null);

		$this->assertSame([], $this->service->getBuckets());
	}

	public function testGetBucketsReturnsEmptyArrayWithoutObjectStore(): void {
		$this->objectStoreConfig->expects($this->once())
			->method('hasObjectStore')
			->willReturn(false);
		$this->objectStoreConfig->expects($this->never())
			->method('getBucketName');
		$this->objectStoreConfig->expects($this->never())
			->method('hasMultipleObjectStorages');

		$this->assertSame([], $this->service->getBuckets());
	}

	public function testGetBucketsUsesBucketNameWhenNotMultiBucket(): void {
		$this->objectStoreConfig->expects($this->once())
			->method('hasObjectStore')
			->willReturn(true);
		$this->objectStoreConfig->expects($this->once())
			->method('getBucketName')
			->willReturn('my-bucket');
		$this->objectStoreConfig->expects($this->once())
			->method('hasMultipleObjectStorages')
			->willReturn(false);
		$this->objectStoreConfig->expects($this->never())
			->method('getMultiBucketPrefix');
		$this->config->expects($this->once())
			->method('getCredentials')
			->willReturn('');

		$this->expectException(\Throwable::class);

		$this->service->getBuckets();
	}

	public function testGetBucketsThrowsWhenCredentialsAreMissingRequiredFields(): void {
		$this->objectStoreConfig->method('hasObjectStore')
			->willReturn(true);
		$this->objectStoreConfig->method('getBucketName')
			->willReturn('my-bucket');
		$this->objectStoreConfig->method('hasMultipleObjectStorages')
			->willReturn(false);
		$this->config->expects($this->once())
			->method('getCredentials')
			->willReturn(json_encode(['client_email' => 'a@b.com']));

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('private_key');

		$this->service->getBuckets();
	}

	public function testGetBucketsUsesMultiBucketPrefixWhenMultiBucket(): void {
		$this->objectStoreConfig->method('hasObjectStore')
			->willReturn(true);
		$this->objectStoreConfig->expects($this->once())
			->method('getBucketName')
			->willReturn(null);
		$this->objectStoreConfig->method('hasMultipleObjectStorages')
			->willReturn(true);
		$this->objectStoreConfig->expects($this->once())
			->method('getMultiBucketPrefix')
			->willReturn('my-bucket-');
		$this->config->expects($this->once())
			->method('getCredentials')
			->willReturn(json_encode(['client_email' => 'a@b.com']));

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('private_key');

		$this->service->getBuckets();
	}
}
