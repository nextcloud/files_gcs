<!--
   - SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
   - SPDX-License-Identifier: AGPL-3.0-or-later
-->
<template>
	<NcSettingsSection
		class="settings"
		:description="t('files_gcs', 'Manage your Google Cloud Storage buckets')"
		:name="t('files_gcs', 'Google Cloud Storage')">
		<NcCheckboxRadioSwitch
			v-model="autoclassEnabled"
			type="switch"
			@update:modelValue="saveConfig">
			{{ t('files_gcs', 'Enable autoclass for new buckets') }}
		</NcCheckboxRadioSwitch>
		<NcSelect
			v-model="terminalStorageClass"
			:options="storageClasses"
			:inputLabel="t('files_gcs', 'Terminal Storage Class')"
			:multiple="false"
			@update:modelValue="saveConfig" />
		<div class="settings__importer">
			<input
				ref="importer"
				type="file"
				accept="application/json"
				class="settings__importerInput"
				@change="saveCredentials">
			<NcButton variant="secondary" :disabled="loading" @click="importFile">
				<template v-if="loading" #icon>
					<NcLoadingIcon />
				</template>
				{{ t('files_gcs', credentialsExist ? 'Import new credentials' : 'Import credentials') }}
			</NcButton>
			<div
				v-if="credentialsExist"
				class="settings__importerMessage">
				✓ {{ t('files_gcs', 'Credentials already setup') }}
			</div>
		</div>
	</NcSettingsSection>
</template>

<script setup lang="ts">
import axios from '@nextcloud/axios'
import { t } from '@nextcloud/l10n'
import { generateOcsUrl } from '@nextcloud/router'
import { NcButton, NcCheckboxRadioSwitch, NcLoadingIcon, NcSelect, NcSettingsSection } from '@nextcloud/vue'
import { onMounted, ref } from 'vue'

const autoclassEnabled = ref(false)
const terminalStorageClass = ref('Nearline')
const credentialsExist = ref(false)
const storageClasses = ref(['Nearline', 'Archive'])
const importer = ref(null)
const loading = ref(false)

onMounted(() => {
	loadConfig()
})

/**
 * Emulate a click to trigger file upload
 */
function importFile() {
	importer.value.value = null
	importer.value.click()
}

/**
 * Load config from source
 */
async function loadConfig() {
	loading.value = true
	const { data } = await axios.get(generateOcsUrl('apps/files_gcs/config'))
	autoclassEnabled.value = data.ocs.data.autoclassEnabled
	terminalStorageClass.value = data.ocs.data.terminalStorageClass
	credentialsExist.value = data.ocs.data.credentialsExist
	loading.value = false
}

/**
 * Save the config
 */
async function saveConfig() {
	loading.value = true
	await axios.put(generateOcsUrl('apps/files_gcs/config'), {
		autoclassEnabled: autoclassEnabled.value,
		terminalStorageClass: terminalStorageClass.value,
	})
	loading.value = false
}

/**
 * Save the credentials
 *
 * @param event - A submit event
 */
async function saveCredentials(event: Event) {
	if (!(event.target instanceof HTMLInputElement)) {
		return
	}

	loading.value = true
	const formData = new FormData()
	const file = event.target.files[0]
	formData.append('credentials', file)

	await axios.post(generateOcsUrl('apps/files_gcs/config/credentials'), formData)
	loading.value = false
}
</script>

<style scoped lang="scss">
.settings {
	&__importer {
		margin-top: 16px;
	}

	&__importerInput {
		display: none;
	}

	&__importerMessage {
		color: color-mix(in srgb, var(--color-main-text) 60%, transparent);
		margin-top: 8px;
		margin-inline-start: 16px;
	}
}
</style>
