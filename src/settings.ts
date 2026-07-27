/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { createApp } from 'vue'
import SettingsView from './views/SettingsView.vue'

const app = createApp(SettingsView)
app.mount('#files-gcs-settings')
