<template>
  <div v-if="selectedTable?.settings?.general">
    <!-- BACKGROUND COLOR -->
    <DashboardSettingsSelectColor
      :label="$t('background_color')"
      :tooltip="$t('messages.set_background_color_for_table')"
      :colors="COLORS"
      :selected-color="selectedTable.settings.general.backgroundColor"
      @select-color="selectedTable.settings.general.backgroundColor = $event"
    />

    <!-- TEXT COLOR -->
    <DashboardSettingsSelectColor
      :label="$t('text_color')"
      :tooltip="$t('messages.set_text_color_for_table')"
      :colors="COLORS"
      :selected-color="selectedTable.settings.general.textColor"
      @select-color="selectedTable.settings.general.textColor = $event"
    />

    <q-separator class="q-mb-md" />

    <!-- DENSE -->
    <DashboardSettingsToggle
      v-model="selectedTable.settings.general.dense"
      :label="$t('dense')"
      :tooltip="$t('messages.applies_dense_mode')"
      :disable="selectedTable.settings.general.grid"
      :show-banner="selectedTable.settings.general.grid"
      :banner-message="$t('messages.available_for_table_view_only')"
    />

    <q-separator class="q-mb-md" />

    <!-- FLAT -->
    <DashboardSettingsToggle
      v-model="selectedTable.settings.general.flat"
      :label="$t('flat')"
      :tooltip="$t('messages.applies_flat_design')"
    />

    <q-separator class="q-mb-md" />

    <!-- SEPARATOR -->
    <DashboardSettingsSelect
      v-model="selectedTable.settings.general.separator"
      class="text-capitalize"
      :options="[
        { label: $t('horizontal'), value: 'horizontal' },
        { label: $t('vertical'), value: 'vertical' },
        { label: $t('cell'), value: 'cell' },
        { label: $t('none'), value: 'none' },
      ]"
      :label="$t('separator')"
      :tooltip="$t('messages.use_separator_between_rows_cols_cells')"
      :disable="selectedTable.settings.general.grid"
      :show-banner="selectedTable.settings.general.grid"
      :banner-message="$t('messages.available_for_table_view_only')"
      emit-value
    />
  </div>
</template>

<script setup lang="ts">
import { storeToRefs } from 'pinia';
import { useDashboardStore } from '../store';
import DashboardSettingsToggle from './DashboardSettingsToggle.vue';
import { COLORS } from '../store/constants';
import DashboardSettingsSelect from './DashboardSettingsSelect.vue';
import DashboardSettingsSelectColor from './DashboardSettingsSelectColor.vue';

/**
 * DASHBOARD STORE
 */
const dashboardStore = useDashboardStore();
const { selectedTable } = storeToRefs(dashboardStore);
</script>
