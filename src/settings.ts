/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import Vue from 'vue'
import SettingsView from './views/SettingsView.vue'

Vue.mixin({ methods: { t, n } })

const View = Vue.extend(SettingsView)
new View().$mount('#files-gcs-settings')
