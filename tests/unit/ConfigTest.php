<?php

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace OCA\FilesGCS\Tests;

use OCA\FilesGCS\Config;
use OCP\AppFramework\Services\IAppConfig;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

final class ConfigTest extends TestCase {
	private IAppConfig&MockObject $appConfig;

	private Config $config;

	public function setUp(): void {
		parent::setUp();

		$this->appConfig = $this->createMock(IAppConfig::class);
		$this->config = new Config($this->appConfig);
	}

	public function testSetAutoclassEnabled(): void {
		$this->appConfig->expects($this->once())
			->method('setAppValueBool')
			->with('autoclass_enabled', true);

		$this->config->setAutoclassEnabled(true);
	}

	public function testSetTerminalStorageClass(): void {
		$this->appConfig->expects($this->once())
			->method('setAppValueString')
			->with('terminal_storage_class', 'Archive');

		$this->config->setTerminalStorageClass('Archive');
	}

	public function testSetCredentialsIsStoredAsSensitive(): void {
		$this->appConfig->expects($this->once())
			->method('setAppValueString')
			->with('credentials', '{"client_email":"a@b.com"}', false, true);

		$this->config->setCredentials('{"client_email":"a@b.com"}');
	}

	public function testGetAutoclassEnabledDefaultsToFalse(): void {
		$this->appConfig->expects($this->once())
			->method('getAppValueBool')
			->with('autoclass_enabled', false)
			->willReturn(false);

		$this->assertFalse($this->config->getAutoclassEnabled());
	}

	public function testGetAutoclassEnabledReturnsStoredValue(): void {
		$this->appConfig->expects($this->once())
			->method('getAppValueBool')
			->with('autoclass_enabled', false)
			->willReturn(true);

		$this->assertTrue($this->config->getAutoclassEnabled());
	}

	public function testGetTerminalStorageClassDefaultsToNearline(): void {
		$this->appConfig->expects($this->once())
			->method('getAppValueString')
			->with('terminal_storage_class', 'Nearline')
			->willReturn('Nearline');

		$this->assertSame('Nearline', $this->config->getTerminalStorageClass());
	}

	public function testGetTerminalStorageClassReturnsStoredValue(): void {
		$this->appConfig->expects($this->once())
			->method('getAppValueString')
			->with('terminal_storage_class', 'Nearline')
			->willReturn('Archive');

		$this->assertSame('Archive', $this->config->getTerminalStorageClass());
	}

	public function testGetCredentialsDefaultsToEmptyString(): void {
		$this->appConfig->expects($this->once())
			->method('getAppValueString')
			->with('credentials', '')
			->willReturn('');

		$this->assertSame('', $this->config->getCredentials());
	}

	public function testGetCredentialsReturnsStoredValue(): void {
		$this->appConfig->expects($this->once())
			->method('getAppValueString')
			->with('credentials', '')
			->willReturn('{"client_email":"a@b.com"}');

		$this->assertSame('{"client_email":"a@b.com"}', $this->config->getCredentials());
	}
}
