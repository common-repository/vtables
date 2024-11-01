<template>
  <div
    class="vtable"
    :class="[`vtable-${selectedTable?.id}`, { 'settings-open': showSettings }]"
  >
    <!-- GO BACK -->
    <div class="row q-mb-md">
      <div class="col">
        <q-btn
          icon="keyboard_backspace"
          color="primary"
          flat
          no-caps
          @click="goBack"
        >
          <span class="q-pl-xs">{{ $t('back_to_dashboard') }}</span>
        </q-btn>
      </div>
    </div>

    <!-- ACTIONS -->
    <div class="row items-center">
      <div class="col-12">
        <DashboardTableActions
          v-if="selectedTable"
          :selected-table="selectedTable"
          :show-settings="showSettings"
          @fetch-selected-table="onFetchTable"
          @change-title="showTitleDialog = true"
          @show-settings="onShowSettings"
        />
      </div>
    </div>

    <!-- ALERT -->
    <q-banner
      v-if="
        selectedTable?.table_type &&
        (selectedTable.table_type === 'woocommerce' ||
          selectedTable.table_type === 'google_sheets')
      "
      inline-actions
      rounded
      class="text-orange-8 q-mb-md"
      style="border: 1px solid"
    >
      <span>
        Sorry, this table type available with
        <span class="text-weight-bold">vTables Pro Add-on</span> only.
      </span>

      <template #action>
        <q-btn
          flat
          class="text-weight-bold"
          color="orange-8"
          label="Download"
          target="_blank"
          href="https://vtables.pro"
        />
      </template>
    </q-banner>

    <!-- TABLE -->
    <q-table
      v-if="selectedTable"
      v-model:pagination="settings.bottom.initial"
      :class="tableCssClasses"
      :loading="loading"
      :rows="selectedTable.source.rows"
      :columns="selectedTable.source.columns"
      :grid="settings.general.grid"
      :table-style="tableStyle"
      :card-class="cardClasses"
      :card-style="cardStyle"
      :dense="settings.general.dense"
      :flat="settings.general.flat || !selectedTable.source.rows.length"
      :separator="settings.general.separator"
    >
      <!-- TOP -->
      <template #top>
        <div class="vtable__top | full-width flex">
          <div class="col">
            <h1
              class="q-ma-xs text-h5"
              :class="`text-${settings.general.textColor}`"
            >
              {{ selectedTable.title }}
            </h1>
          </div>
        </div>
      </template>

      <!-- BODY -->
      <template #body="props">
        <q-tr :props="props">
          <q-td v-for="col in props.cols" :key="col.name" :props="props">
            {{ props.row[col.field] || props.row[col.label] }}
          </q-td>
        </q-tr>
      </template>

      <!-- GRID -->
      <template v-if="settings.general.grid" #item="props">
        <div
          class="vtable__grid | q-pa-xs col-xs-12 col-sm-4 grid-style-transition"
        >
          <q-card
            class="q-pt-sm full-height"
            :flat="settings.general.flat || !selectedTable.source.rows.length"
            :class="`bg-${settings.general.backgroundColor}`"
          >
            <q-list>
              <q-item v-for="col in props.cols" :key="col.name">
                <q-item-section v-if="col.value">
                  <q-item-label
                    :class="`text-${settings.general.textColor}`"
                    caption
                  >
                    {{ col.label }}
                  </q-item-label>
                  <q-item-label :class="`text-${settings.general.textColor}`">
                    <div class="cell-value">
                      {{ col.value }}
                    </div>
                  </q-item-label>
                </q-item-section>
              </q-item>
            </q-list>
          </q-card>
        </div>
      </template>
    </q-table>

    <q-table v-else :loading="loading" :rows="[]" :columns="[]" />

    <!-- SETTINGS -->
    <DashboardSettings
      :show-settings="showSettings"
      @close="showSettings = !showSettings"
    />

    <!-- CHANGE TITLE DIALOGUE -->
    <DashboardTableActionsRename
      v-if="selectedTable"
      v-model="showTitleDialog"
      :title="selectedTable.title || ''"
      :loading="loading"
      @update="updateTableName"
    />
  </div>
</template>

<script setup lang="ts">
import { ComputedRef, Ref, computed, onMounted, ref } from 'vue';
import { Router, useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useQuasar } from 'quasar';
import { storeToRefs } from 'pinia';
import { useDashboardStore } from '../store';
import {
  FETCH_TABLE,
  INITIAL_SETTINGS,
  SAVE_TABLE_CHANGES,
} from '../store/constants';
import DashboardSettings from './DashboardSettings.vue';
import DashboardTableActions from './DashboardTableActions.vue';
import DashboardTableActionsRename from './DashboardTableActionsRename.vue';
import { Settings } from '../store/models';

/**
 * DASHBOARD STORE
 */
const dashboardStore = useDashboardStore();
const fetchTable = dashboardStore[FETCH_TABLE];
const saveTableChanges = dashboardStore[SAVE_TABLE_CHANGES];
const { selectedTable } = storeToRefs(dashboardStore);

/**
 * CONSTANTS
 */
const { t } = useI18n();
const $q = useQuasar();
const router: Router = useRouter();
const loading: Ref<boolean> = ref(false);
const showSettings: Ref<boolean> = ref(false);
const tableMaximized: Ref<boolean> = ref(false);
const showTitleDialog: Ref<boolean> = ref(false);
const tableId: ComputedRef<string> = computed(() => {
  const { params } = router.currentRoute.value;
  return Array.isArray(params.id) ? params.id[0] : params.id;
});
const settings: ComputedRef<Settings> = computed(() => {
  if (selectedTable.value) {
    return selectedTable.value.settings;
  }
  return INITIAL_SETTINGS;
});
const tableCssClasses: ComputedRef<any[]> = computed(() => {
  const isAdminbarPresented = document.querySelector('#wpadminbar');
  return [
    {
      'admin-bar-presented-on-fullscreen':
        tableMaximized.value && isAdminbarPresented,
    },
  ];
});
const tableStyle: ComputedRef<Record<string, string>> = computed(() => {
  return {
    height: settings.value.dimensions.tableHeight
      ? settings.value.dimensions.tableHeight + 'px'
      : '100%',
  };
});
const cardClasses: ComputedRef<string[]> = computed(() => {
  const cssClasses = [];

  if (settings.value.general.backgroundColor) {
    cssClasses.push(`bg-${settings.value.general.backgroundColor}`);
  }

  if (settings.value.general.textColor) {
    cssClasses.push(`text-${settings.value.general.textColor}`);
  }

  return cssClasses;
});
const cardStyle: ComputedRef<Record<string, string>> = computed(() => {
  return {
    maxWidth: settings.value.dimensions.tableMaxWidth
      ? settings.value.dimensions.tableMaxWidth + 'px'
      : '100%',
  };
});

/**
 * MOUNTED lifecycle-hook
 */
onMounted(async () => {
  if (selectedTable.value) {
    return;
  }

  await onFetchTable(true);
});

/**
 * FETCH TABLE
 */
async function onFetchTable(initialReq?: boolean) {
  loading.value = true;
  const { error } = await fetchTable(tableId.value);
  loading.value = false;

  if (error) {
    $q.notify({
      type: 'negative',
      message: t('messages.unable_to_fetch_table'),
    });

    return;
  }

  if (!initialReq) {
    $q.notify({
      type: 'positive',
      message: t('messages.data_has_been_synced'),
    });
  }
}

/**
 * GO BACK
 */
function goBack() {
  selectedTable.value = null;
  router.push('/');
}

/**
 * UPDATE TABLE NAME
 */
async function updateTableName(title: string) {
  if (!selectedTable.value) {
    return;
  }

  loading.value = true;

  const { error } = await saveTableChanges({
    id: selectedTable.value.id,
    title,
  });

  loading.value = false;

  if (error) {
    $q.notify({
      type: 'negative',
      message: t('messages.unable_to_save_changes'),
      caption: t('messages.something_went_wrong'),
    });

    return;
  }

  $q.notify({
    type: 'positive',
    message: t('messages.changes_have_been_saved'),
  });

  selectedTable.value.title = title;

  showTitleDialog.value = false;
}

/**
 * SHOW SETTINGS
 */
function onShowSettings() {
  showSettings.value = !showSettings.value;
}
</script>

<style lang="scss">
.settings-open {
  margin-right: 325px;
}

.cell-value {
  display: block;
  white-space: initial;
  width: inherit;
}

.admin-bar-presented-on-fullscreen {
  margin-top: 30px;
  margin-left: 160px;
}

.fullscreen-btn {
  height: 40px;
  width: 40px;
}
</style>
