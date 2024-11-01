<template>
  <q-drawer
    :model-value="showSettings"
    class="settings-bar | bg-grey-2 q-mt-lg q-py-sm"
    side="right"
    bordered
    overlay
    :width="$q.screen.lt.sm ? $q.screen.width : 320"
    :breakpoint="0"
    style="visibility: visible !important"
  >
    <q-scroll-area v-if="showSettings" class="fit">
      <q-splitter
        class="splitter"
        :model-value="32"
        :style="{ height: $q.screen.height - 20 + 'px' }"
      >
        <template #before>
          <q-tabs
            v-model="activeTab"
            dense
            class="text-grey bg-grey-2"
            active-color="primary"
            indicator-color="primary"
            vertical
          >
            <q-tab
              name="columns"
              :label="$t('columns')"
              alert="gray"
              alert-icon="lock"
              disable
              title="Available for PREMIUM plans."
            />
            <q-tab name="dimensions" :label="$t('dimensions')" />
            <q-tab
              name="export"
              :label="$t('export')"
              alert="gray"
              alert-icon="lock"
              disable
              title="Available for PREMIUM plans."
            />
            <q-tab
              name="pagination"
              :label="$t('pagination')"
              alert="gray"
              alert-icon="lock"
              disable
              title="Available for PREMIUM plans."
            />
            <q-tab
              name="rows"
              :label="$t('rows')"
              alert="gray"
              alert-icon="lock"
              disable
              title="Available for PREMIUM plans."
            />
            <q-tab
              name="search"
              :label="$t('search')"
              alert="gray"
              alert-icon="lock"
              disable
              title="Available for PREMIUM plans."
            />
            <q-tab
              name="sorting"
              :label="$t('sorting')"
              alert="gray"
              alert-icon="lock"
              disable
              title="Available for PREMIUM plans."
            />
            <q-tab name="style" :label="$t('style')" />
            <q-tab
              name="top"
              :label="$t('top')"
              alert="gray"
              alert-icon="lock"
              disable
              title="Available for PREMIUM plans."
            />
            <q-tab name="view" :label="$t('view')" />
            <q-tab name="reset" :label="$t('reset')" />
          </q-tabs>
        </template>
        <template #after>
          <q-tab-panels v-model="activeTab" class="full-height" vertical>
            <q-tab-panel name="style">
              <DashboardSettingsGeneral />
            </q-tab-panel>
            <q-tab-panel name="dimensions">
              <DashboardSettingsDimensions />
            </q-tab-panel>
            <q-tab-panel name="view">
              <DashboardSettingsView />
            </q-tab-panel>
            <q-tab-panel name="reset">
              <DashboardSettingsReset :loading="loading" @reset="reset" />
            </q-tab-panel>
          </q-tab-panels>
          <div
            v-if="activeTab !== 'export' && activeTab !== 'reset'"
            class="action-buttons | absolute q-mb-sm q-ml-sm"
          >
            <q-btn
              class="full-width q-mb-sm"
              color="primary"
              :loading="loading"
              @click="save"
            >
              <span class="q-px-sm">
                {{ $t('save') }}
              </span>
            </q-btn>
            <q-btn
              class="full-width q-mb-sm"
              color="primary"
              size="sm"
              outline
              @click="cancel"
            >
              <span class="q-px-sm">
                {{ $t('cancel') }}
              </span>
            </q-btn>
          </div>
        </template>
      </q-splitter>
    </q-scroll-area>
  </q-drawer>
</template>

<script setup lang="ts">
import { Ref, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { QTableColumn, useQuasar } from 'quasar';
import { storeToRefs } from 'pinia';
import { useDashboardStore } from '../store';
import {
  FETCH_TABLE,
  SAVE_TABLE_SETTINGS,
  INITIAL_SETTINGS,
} from '../store/constants';
import DashboardSettingsGeneral from './DashboardSettingsGeneral.vue';
import DashboardSettingsDimensions from './DashboardSettingsDimensions.vue';
import DashboardSettingsReset from './DashboardSettingsReset.vue';
import DashboardSettingsView from './DashboardSettingsView.vue';

/**
 * PROPS
 */
defineProps<{
  showSettings: boolean;
}>();

/**
 * EMITS
 */
defineEmits(['close']);

/**
 * DASHBOARD STORE
 */
const dashboardStore = useDashboardStore();
const fetchTable = dashboardStore[FETCH_TABLE];
const saveTableSettings = dashboardStore[SAVE_TABLE_SETTINGS];
const { selectedTable, loading } = storeToRefs(dashboardStore);

/**
 * CONSTANTS
 */
const { t } = useI18n();
const $q = useQuasar();
const activeTab: Ref<string> = ref('style');

/**
 * SAVE
 */
async function save() {
  if (!selectedTable.value) {
    return;
  }

  if (selectedTable.value.settings) {
    loading.value = true;
    const { error } = await saveTableSettings({
      id: selectedTable.value.id,
      settings: selectedTable.value.settings,
    });
    loading.value = false;

    if (error) {
      $q.notify({
        type: 'negative',
        message: t('messages.unable_to_save_changes'),
      });

      return;
    }

    $q.notify({
      type: 'positive',
      message: t('messages.settings_have_been_saved'),
    });
  }
}

/**
 * CANCEL
 */
async function cancel() {
  if (!selectedTable.value) {
    return;
  }

  const { error } = await fetchTable(selectedTable.value.id);

  if (error) {
    $q.notify({
      type: 'negative',
      message: t('messages.unable_to_cancel_changes'),
    });
  }
}

/**
 * RESET
 */
async function reset() {
  if (!selectedTable.value) {
    return;
  }

  loading.value = true;

  const visibleColumns = selectedTable.value.source.columns?.map(
    (el: QTableColumn) => el.name
  );

  const settings = {
    ...INITIAL_SETTINGS,
    columns: { visibleColumns },
  };

  const { error } = await saveTableSettings({
    id: selectedTable.value.id,
    settings,
  });

  loading.value = false;

  if (error) {
    $q.notify({
      type: 'negative',
      message: t('messages.unable_to_reset_settings'),
    });

    return;
  }

  const fetchTableResponse = await fetchTable(selectedTable.value.id);

  if (fetchTableResponse.error) {
    $q.notify({
      type: 'negative',
      message: t('messages.unable_to_cancel_changes'),
    });

    return;
  }

  $q.notify({
    type: 'positive',
    message: t('messages.settings_have_been_reset'),
  });
}
</script>

<style scoped>
.action-buttons {
  right: 10px;
  bottom: 10px;
  width: 90%;
}

.backdrop {
  background: transparent;
  height: 100%;
  position: fixed;
  right: 310px;
  top: 0;
  z-index: 9999;
  width: 100%;
}

.q-tab-panels {
  padding-bottom: 110px;
}

.q-tab {
  justify-content: flex-start;
}
</style>

<style lang="scss">
.splitter .q-tab__label {
  font-size: 12px;
}

.settings-bar {
  .q-tabs__content {
    .q-tab:last-child {
      border-top: 1px solid #e1e1e1;
    }
    .q-tab__alert-icon {
      margin-right: -5px;
      font-size: 13px;
      margin-top: 8px;
      position: absolute;
    }
  }
}
</style>
