<template>
  <div v-if="selectedTable?.settings?.dimensions" class="settings-style-tab">
    <!-- TABLE HEIGHT -->
    <DashboardSettingsInput
      v-model="selectedTable.settings.dimensions.tableHeight"
      :label="$t('table_height')"
      :tooltip="$t('messages.set_table_height')"
      type="number"
    >
      <template #append>
        <span class="text-caption">px</span>
      </template>
    </DashboardSettingsInput>

    <!-- TABLE MAX WIDTH -->
    <DashboardSettingsInput
      v-model="selectedTable.settings.dimensions.tableMaxWidth"
      :label="$t('table_width')"
      :tooltip="$t('messages.set_table_max_width')"
      type="number"
    >
      <template #append>
        <span class="text-caption">px</span>
      </template>
    </DashboardSettingsInput>
  </div>
</template>

<script setup lang="ts">
import { storeToRefs } from 'pinia';
import { useDashboardStore } from '../store';
import DashboardSettingsInput from './DashboardSettingsInput.vue';

/**
 * DASHBOARD STORE
 */
const dashboardStore = useDashboardStore();
const { selectedTable } = storeToRefs(dashboardStore);
</script>

<style lang="scss">
.settings-style-tab {
  .q-field__native,
  .q-field__native > span {
    text-overflow: ellipsis;
    white-space: nowrap;
    overflow: hidden;
  }
}
</style>
