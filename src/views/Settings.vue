<!--
   - SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
   - SPDX-License-Identifier: AGPL-3.0-or-later
-->
<template>
  <NcSettingsSection class="settings"
					 :description="t('files_gcs', 'Manage your Google Cloud Storage buckets')"
					 :name="t('files_gcs', 'Google Cloud Storage')">
	<NcCheckboxRadioSwitch type="switch"
						   @update:modelValue="saveConfig"
						   v-model="autoclassEnabled">
	  {{ t('files_gcs', 'Enable autoclass for new buckets') }}
	</NcCheckboxRadioSwitch>
	<NcSelect v-model="terminalStorageClass"
			  :options="storageClasses"
			  :input-label="t('files_gcs', 'Terminal Storage Class')"
			  :multiple="false"
			  @update:modelValue="saveConfig" />
	<div class="settings__importer">
	  <input type="file"
			 accept="application/json"
			 ref="importer"
			 class="settings__importerInput"
			 @change="saveCredentials">
	  <NcButton variant="secondary" @click="importFile" :disabled="loading">
		  <template #icon v-if="loading"><NcLoadingIcon /></template>
		{{ t('files_gcs', credentialsExist ? 'Import new credentials' : 'Import credentials') }}
	  </NcButton>
	  <div class="settings__importerMessage"
		   v-if="credentialsExist">
		✓ {{ t('files_gcs', 'Credentials already setup') }}
	  </div>
	</div>
  </NcSettingsSection>
</template>

<script setup lang="ts">
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { ref, onMounted } from 'vue'

import {
	NcButton,
	NcCheckboxRadioSwitch,
	NcLoadingIcon,
	NcSettingsSection,
	NcSelect
} from '@nextcloud/vue'

const autoclassEnabled = ref(false)
const terminalStorageClass = ref('Nearline')
const credentialsExist = ref(false)
const storageClasses = ref(['Nearline', 'Archive'])
const importer = ref(null)
const loading = ref(false)

onMounted(() => {
	loadConfig()
})

function importFile() {
	importer.value.value = null
	importer.value.click()
}

async function loadConfig() {
	loading.value = true
	const { data } = await axios.get(generateUrl('apps/files_gcs/config'))
	autoclassEnabled.value = data.ocs.data.autoclassEnabled
	terminalStorageClass.value = data.ocs.data.terminalStorageClass
	credentialsExist.value = data.ocs.data.credentialsExist
	loading.value = false
}

async function saveConfig() {
	loading.value = true
	const { data } = await axios.put(generateUrl('apps/files_gcs/config'), {
		autoclassEnabled: autoclassEnabled.value,
		terminalStorageClass: terminalStorageClass.value
	})
	loading.value = false
}

async function saveCredentials(event: Event) {
	if (!(event.target instanceof HTMLInputElement)) {
		return
	}

	loading.value = true
	const formData = new FormData()
	let file = event.target.files[0]
	formData.append('credentials', file)

	const { data } = await axios.post(generateUrl('apps/files_gcs/config/credentials'), formData)
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
