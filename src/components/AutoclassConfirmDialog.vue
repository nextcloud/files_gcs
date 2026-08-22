<!--
   - SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
   - SPDX-License-Identifier: AGPL-3.0-or-later
-->
<template>
	<NcDialog
		:buttons="buttons"
		:name="t('files_gcs', 'Confirm Autoclass Update')"
		:message="t('files_gcs', 'Are you sure you want to set autoclass to ' + autoclassStatus + '?')" />
</template>

<script setup lang="ts">
import { t } from '@nextcloud/l10n'
import { NcDialog } from '@nextcloud/vue'
import { computed, defineEmits, defineProps } from 'vue'

defineProps<{ autoclassStatus?: string }>()
const emit = defineEmits(['close'])
const buttons = computed(() => [
	{
		label: t('files_gcs', 'No'),
		type: 'reset',
		variant: 'secondary',
		callback: () => { emit('close', false) },
	},
	{
		label: t('files_gcs', 'Yes'),
		type: 'submit',
		variant: 'primary',
		callback: () => { emit('close', true) },
	},
])
</script>
