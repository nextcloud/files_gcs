/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

interface BucketAutoclass {
	terminalStorageClass?: string
	enabled: boolean
}

interface Bucket {
	name: string
	autoclass?: BucketAutoclass
}

export { Bucket }
