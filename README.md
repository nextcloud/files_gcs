<!--
  - SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-only
-->

# Files GCS

The Files GCS app provides support for features specific to Google Cloud Storage.

## Features
- [Autoclass](https://cloud.google.com/storage/docs/autoclass)
  - Terminal Storage Class

## Setup

[S3 object storage](https://docs.nextcloud.com/server/stable/admin_manual/configuration_files/primary_storage.html) needs to be configured as main storage after Nextcloud is installed

A [service account](https://cloud.google.com/iam/docs/service-accounts-create) is required (along with GCS legacy auth setup above) during setup.

- Once a service account is generated, import them with `occ files_gcs:credentials:import /path/to/credentials.json`
- Enable autoclass support with `occ files_gcs:autoclass:enable` (disabled by default. Once enabled, can be disabled with `occ files_gcs:autoclass:disable`)
- Set the terminal storage class with `occ files_gcs:terminal_storage_class:set storage_class`. Replace `storage_class` with *nearline* (default) or *archive*. This is only applied when `autoclass` is enabled.
