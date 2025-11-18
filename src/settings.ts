/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import Vue from 'vue'

import Settings from './views/Settings.vue'

Vue.mixin({ methods: { t, n } })

const View = Vue.extend(Settings)
new View().$mount('#files-gcs-settings')
