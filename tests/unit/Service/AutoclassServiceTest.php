<?php

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace OCA\FilesGCS\Tests\Service;

use OCA\FilesGCS\Config;
use OCA\FilesGCS\Service\AutoclassService;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Test\TestCase;

final class AutoclassServiceTest extends TestCase {
	private Config&MockObject $config;
	private LoggerInterface&MockObject $logger;

	private AutoclassService $service;

	public function setUp(): void {
		parent::setUp();

		$this->config = $this->createMock(Config::class);
		$this->logger = $this->createMock(LoggerInterface::class);
		$this->service = new AutoclassService(
			$this->config,
			$this->logger,
		);
	}

	public function testEnableFallsBackToConfigCredentialsWhenNoneProvided(): void {
		$this->config->expects($this->once())
			->method('getCredentials')
			->willReturn(json_encode(['client_email' => 'a@b.com']));
		$this->logger->expects($this->never())
			->method('error');

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('private_key');

		$this->service->enable('my-bucket');
	}

	public function testEnablePrefersExplicitlyProvidedCredentialsOverConfig(): void {
		$this->config->expects($this->never())
			->method('getCredentials');
		$this->logger->expects($this->never())
			->method('error');

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('private_key');

		$this->service->enable('my-bucket', json_encode(['client_email' => 'a@b.com']));
	}

	public function testEnableThrowsWhenCredentialsAreMissingClientEmail(): void {
		$this->logger->expects($this->never())
			->method('error');

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('client_email');

		$this->service->enable('my-bucket', json_encode(['private_key' => 'fake']));
	}

	public function testEnableThrowsWhenCredentialsAreEmpty(): void {
		$this->logger->expects($this->never())
			->method('error');

		$this->expectException(\Throwable::class);

		$this->service->enable('my-bucket', '');
	}
}
