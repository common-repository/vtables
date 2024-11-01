<template>
  <div class="row items-top q-mb-md">
    <div :class="$q.screen.gt.sm ? 'col-10' : 'col-11'">
      <div class="flex q-gutter-sm">
        <DashboardTableActionsTitle
          :title="selectedTable.title || ''"
          @change-title="$emit('change-title')"
        />
        <DashboardTableActionsShortcode :shortcode="selectedTable.shortcode" />
        <DashboardTableActionsFetch
          :selected-table="selectedTable"
          @fetch-selected-table="$emit('fetch-selected-table')"
        />
      </div>
    </div>
    <div class="col text-right">
      <q-btn
        class="text-weight-bold rounded-borders q-py-sm q-px-md"
        :title="showSettings ? 'Hide Settings' : 'Show Settings'"
        :label="$q.screen.gt.sm ? 'Settings' : ''"
        :icon="showSettings ? 'chevron_right' : 'tune'"
        color="primary"
        dense
        square
        unelevated
        @click="$emit('show-settings')"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { Table } from '../store/models';
import DashboardTableActionsFetch from './DashboardTableActionsFetch.vue';
import DashboardTableActionsShortcode from './DashboardTableActionsShortcode.vue';
import DashboardTableActionsTitle from './DashboardTableActionsTitle.vue';

/**
 * PROPS
 */
defineProps<{
  selectedTable: Table;
  showSettings: boolean;
}>();

/**
 * EMITS
 */
defineEmits(['fetch-selected-table', 'change-title', 'show-settings']);
</script>
