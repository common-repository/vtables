<template>
  <div v-if="selectedTable?.settings?.general">
    <!-- VIEW -->
    <DashboardSettingsToggle
      v-model="selectedTable.settings.general.grid"
      :label="$t('view')"
      :tooltip="$t('messages.display_data_as_table_or_grid')"
      :options="[
        { label: $t('table'), value: false },
        { label: $t('grid'), value: true },
      ]"
    />
  </div>
</template>

<script setup lang="ts">
import { storeToRefs } from 'pinia';
import { useDashboardStore } from '../store';
import DashboardSettingsToggle from './DashboardSettingsToggle.vue';

/**
 * DASHBOARD STORE
 */
const dashboardStore = useDashboardStore();
const { selectedTable } = storeToRefs(dashboardStore);
</script>
