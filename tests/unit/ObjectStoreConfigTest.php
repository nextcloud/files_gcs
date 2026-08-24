<?php

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace OCA\FilesGCS\Tests;

use OCA\FilesGCS\ObjectStoreConfig;
use OCP\IConfig;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

class ObjectStoreConfigTest extends TestCase {
	private IConfig&MockObject $config;

	private ObjectStoreConfig $objectStoreConfig;

	public function setUp(): void {
		parent::setUp();

		$this->config = $this->createMock(IConfig::class);
		$this->objectStoreConfig = new ObjectStoreConfig($this->config);
	}

	private function mockSystemValues(?array $objectStore, ?array $objectStoreMultiBucket = null): void {
		$this->config->method('getSystemValue')
			->willReturnMap([
				['objectstore', null, $objectStore],
				['objectstore_multibucket', null, $objectStoreMultiBucket],
			]);
	}

	public function testHasObjectStoreReturnsFalseWithoutAnyConfig(): void {
		$this->mockSystemValues(null, null);

		$this->assertFalse($this->objectStoreConfig->hasObjectStore());
	}

	public function testHasObjectStoreReturnsFalseForNonGcsHostname(): void {
		$this->mockSystemValues([
			'arguments' => ['hostname' => 's3.amazonaws.com', 'bucket' => 'my-bucket'],
		]);

		$this->assertFalse($this->objectStoreConfig->hasObjectStore());
	}

	public function testHasObjectStoreReturnsFalseWhenHostnameIsMissing(): void {
		$this->mockSystemValues([
			'arguments' => ['bucket' => 'my-bucket'],
		]);

		$this->assertFalse($this->objectStoreConfig->hasObjectStore());
	}

	public function testHasObjectStoreReturnsTrueForSingleBucketGcsConfig(): void {
		$this->mockSystemValues([
			'arguments' => ['hostname' => 'storage.googleapis.com', 'bucket' => 'my-bucket'],
		]);

		$this->assertTrue($this->objectStoreConfig->hasObjectStore());
	}

	public function testHasObjectStoreReturnsTrueForLegacyMultiBucketConfig(): void {
		$this->mockSystemValues(null, [
			'arguments' => ['hostname' => 'storage.googleapis.com', 'bucket' => 'my-bucket-'],
		]);

		$this->assertTrue($this->objectStoreConfig->hasObjectStore());
	}

	public function testGetBucketNameReturnsNullWithoutAnyConfig(): void {
		$this->mockSystemValues(null, null);

		$this->assertNull($this->objectStoreConfig->getBucketName());
	}

	public function testGetBucketNameReturnsNullForNonGcsHostname(): void {
		$this->mockSystemValues([
			'arguments' => ['hostname' => 's3.amazonaws.com', 'bucket' => 'my-bucket'],
		]);

		$this->assertNull($this->objectStoreConfig->getBucketName());
	}

	public function testGetBucketNameReturnsNullWhenOnlyLegacyMultiBucketConfigIsSet(): void {
		$this->mockSystemValues(null, [
			'arguments' => ['hostname' => 'storage.googleapis.com', 'bucket' => 'my-bucket-'],
		]);

		$this->assertNull($this->objectStoreConfig->getBucketName());
	}

	public function testGetBucketNameReturnsConfiguredBucket(): void {
		$this->mockSystemValues([
			'arguments' => ['hostname' => 'storage.googleapis.com', 'bucket' => 'my-bucket'],
		]);

		$this->assertSame('my-bucket', $this->objectStoreConfig->getBucketName());
	}

	public function testGetBucketNameReturnsNullWhenBucketArgumentIsMissing(): void {
		$this->mockSystemValues([
			'arguments' => ['hostname' => 'storage.googleapis.com'],
		]);

		$this->assertNull($this->objectStoreConfig->getBucketName());
	}

	public function testGetMultiBucketPrefixReturnsNullWithoutAnyConfig(): void {
		$this->mockSystemValues(null, null);

		$this->assertNull($this->objectStoreConfig->getMultiBucketPrefix());
	}

	public function testGetMultiBucketPrefixReturnsNullForNonGcsHostname(): void {
		$this->mockSystemValues([
			'arguments' => ['hostname' => 's3.amazonaws.com', 'bucket' => 'my-bucket-', 'multibucket' => true],
		]);

		$this->assertNull($this->objectStoreConfig->getMultiBucketPrefix());
	}

	public function testGetMultiBucketPrefixReturnsPrefixWhenObjectStoreIsMultiBucket(): void {
		$this->mockSystemValues([
			'arguments' => ['hostname' => 'storage.googleapis.com', 'bucket' => 'my-bucket-', 'multibucket' => true],
		]);

		$this->assertSame('my-bucket-', $this->objectStoreConfig->getMultiBucketPrefix());
	}

	public function testGetMultiBucketPrefixFallsBackToLegacyMultiBucketConfig(): void {
		$this->mockSystemValues(
			['arguments' => ['hostname' => 'storage.googleapis.com', 'bucket' => 'single-bucket']],
			['arguments' => ['hostname' => 'storage.googleapis.com', 'bucket' => 'my-bucket-']],
		);

		$this->assertSame('my-bucket-', $this->objectStoreConfig->getMultiBucketPrefix());
	}

	public function testGetMultiBucketPrefixReturnsNullWhenObjectStoreIsNotMultiBucketAndNoLegacyConfigExists(): void {
		$this->mockSystemValues([
			'arguments' => ['hostname' => 'storage.googleapis.com', 'bucket' => 'single-bucket'],
		]);

		$this->assertNull($this->objectStoreConfig->getMultiBucketPrefix());
	}

	public function testGetMultiBucketPrefixReturnsNullWhenLegacyBucketArgumentIsMissing(): void {
		$this->mockSystemValues(null, [
			'arguments' => ['hostname' => 'storage.googleapis.com'],
		]);

		$this->assertNull($this->objectStoreConfig->getMultiBucketPrefix());
	}

	public function testHasMultipleObjectStoragesReturnsFalseWithoutAnyConfig(): void {
		$this->mockSystemValues(null, null);

		$this->assertFalse($this->objectStoreConfig->hasMultipleObjectStorages());
	}

	public function testHasMultipleObjectStoragesReturnsFalseForNonGcsHostname(): void {
		$this->mockSystemValues([
			'arguments' => ['hostname' => 's3.amazonaws.com', 'multibucket' => true],
		]);

		$this->assertFalse($this->objectStoreConfig->hasMultipleObjectStorages());
	}

	public function testHasMultipleObjectStoragesReturnsTrueWhenObjectStoreIsMultiBucket(): void {
		$this->mockSystemValues([
			'arguments' => ['hostname' => 'storage.googleapis.com', 'multibucket' => true],
		]);

		$this->assertTrue($this->objectStoreConfig->hasMultipleObjectStorages());
	}

	public function testHasMultipleObjectStoragesReturnsFalseWhenObjectStoreIsSingleBucket(): void {
		$this->mockSystemValues([
			'arguments' => ['hostname' => 'storage.googleapis.com'],
		]);

		$this->assertFalse($this->objectStoreConfig->hasMultipleObjectStorages());
	}

	public function testHasMultipleObjectStoragesReturnsTrueForLegacyMultiBucketConfig(): void {
		$this->mockSystemValues(null, [
			'arguments' => ['hostname' => 'storage.googleapis.com', 'bucket' => 'my-bucket-'],
		]);

		$this->assertTrue($this->objectStoreConfig->hasMultipleObjectStorages());
	}
}
