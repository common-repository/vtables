<template>
  <div class="text-right" :class="{ invisible: !totalInDb }">
    <q-btn icon="add_box" color="primary" @click="showUploader = true">
      <span class="q-pl-xs">{{ $t('add_table') }}</span>
    </q-btn>
  </div>
  <div class="row">
    <div class="col">
      <q-table
        class="q-mt-md"
        v-model:pagination="pagination"
        v-model:selected="selected"
        :selection="tables.length ? 'multiple' : 'none'"
        :loading="loading"
        :rows="tables"
        :columns="columns"
        :filter="searchQuery"
        :hide-bottom="!tables.length"
        :rows-per-page-options="[5, 10, 25, 50]"
        row-key="id"
        flat
        @request="onRequest"
      >
        <template #top-left>
          <span class="text-h6">{{ $t('tables') }}</span>
          <q-btn
            v-if="selected.length"
            class="q-ml-md"
            :loading="loading"
            :disable="loading"
            icon="delete"
            color="negative"
            outline
            dense
          >
            {{ $t('remove_selected') }}
            <q-popup-proxy ref="proxy" style="width: 300px">
              <q-banner class="text-white bg-red">
                <b>{{ $t('remove') }}</b>
                {{ $t('messages.all_selected_tables') }}?
                <template #action>
                  <div class="flex justify-between full-width">
                    <q-btn flat :label="$t('cancel')" size="sm" v-close-popup />
                    <q-btn
                      outline
                      :label="$t('confirm')"
                      size="sm"
                      @click="removeSelected()"
                    />
                  </div>
                </template>
              </q-banner>
            </q-popup-proxy>
          </q-btn>
        </template>
        <template #top-right>
          <q-input
            v-model="searchQuery"
            input-class="search-input"
            outlined
            dense
            debounce="500"
            :placeholder="$t('search_by_title')"
          >
            <template #append>
              <q-icon
                v-if="searchQuery"
                name="cancel"
                class="cursor-pointer z-top"
                @click.stop.prevent="searchQuery = ''"
              />
              <q-icon v-else name="search" />
            </template>
          </q-input>
        </template>
        <template v-if="!tables.length && !loading" #top-row>
          <q-tr>
            <q-td colspan="8">
              <div v-if="totalInDb" class="q-py-md text-center">
                <span class="block q-mb-sm text-h6">
                  {{ $t('nothing_found') }}.
                </span>
              </div>
              <div v-else class="q-py-md text-center">
                <span class="block q-mb-sm text-h6">
                  {{ $t('messages.add_your_first_table') }}
                </span>
                <q-btn
                  icon="add_box"
                  color="primary"
                  @click="showUploaderPopup"
                >
                  <span class="q-pl-xs">{{ $t('add_table') }}</span>
                </q-btn>
              </div>
            </q-td>
          </q-tr>
        </template>
        <template #body="props">
          <q-tr
            class="cursor-pointer"
            :props="props"
            @click="onRowClick(props.row)"
          >
            <q-td>
              <q-checkbox v-model="props.selected" />
            </q-td>
            <q-td>
              <div class="ellipsis" style="width: 250px">
                {{ props.row.title }}
              </div>
            </q-td>
            <q-td>
              <template v-if="props.row.table_type">
                <q-chip v-if="props.row.table_type === 'local'" dense square>
                  csv
                </q-chip>
                <q-chip
                  v-else-if="props.row.table_type === 'remote'"
                  dense
                  square
                >
                  google sheets
                </q-chip>
                <q-chip v-else class="text-lowercase" dense square>
                  {{ props.row.table_type?.replace('_', ' ') }}
                </q-chip>
              </template>
              <template v-else>
                <q-chip class="text-lowercase" dense square> csv </q-chip>
              </template>
            </q-td>
            <q-td>
              {{ props.row.shortcode }}
              <q-btn
                color="grey"
                flat
                round
                size="sm"
                title="Copy"
                :icon="
                  copiedShortcode === props.row.shortcode
                    ? 'check'
                    : 'content_copy'
                "
                @click.stop="copyToBuffer(props.row.shortcode)"
                @focusout="copiedShortcode = ''"
              />
            </q-td>
            <q-td>
              {{ props.row.file_size }}
            </q-td>
            <q-td>
              {{ props.row.created_at }}
            </q-td>
            <q-td>
              {{ props.row.updated_at }}
            </q-td>
            <q-td>
              <DashboardActions
                :loading="loading"
                :tr-props="{ props }"
                @selectTable="onRowClick"
                @deleteTable="deleteTable"
              />
            </q-td>
          </q-tr>
        </template>
      </q-table>
    </div>
  </div>
  <DashboardUploader
    :show="showUploader"
    @close="showUploader = false"
    @upload="onFetchTables"
  />
</template>

<script setup lang="ts">
import { Ref, onMounted, ref } from 'vue';
import { copyToClipboard, QTableColumn, QTableProps } from 'quasar';
import { useI18n } from 'vue-i18n';
import { Router, useRouter } from 'vue-router';
import { storeToRefs } from 'pinia';
import { useStorage } from '@vueuse/core';
import { useDashboardStore } from '../store';
import { FETCH_TABLES, DELETE_TABLE } from '../store/constants';
import { Table, Pagination } from '../store/models';
import DashboardActions from './DashboardActions.vue';
import DashboardUploader from './DashboardUploader.vue';

/**
 * EXPOSE
 */
defineExpose({ showUploaderPopup });

/**
 * DASHBOARD STORE
 */
const dashboardStore = useDashboardStore();
const fetchTables = dashboardStore[FETCH_TABLES];
const deleteTable = dashboardStore[DELETE_TABLE];
const {
  loading,
  tables,
  totalPerQuery,
  totalInDb,
  copiedShortcode,
  selectedTable,
} = storeToRefs(dashboardStore);

/**
 * CONSTANTS
 */
const { t } = useI18n();
const router: Router = useRouter();
const showUploader: Ref<boolean> = ref(false);
const selected: Ref<Table[]> = ref([]);
const searchQuery: Ref<string> = ref('');
const recordsPerPage = useStorage('recordsPerPage', 5);
const pagination: Ref<Pagination> = ref({
  sortBy: 'created_at',
  descending: true,
  page: 1,
  rowsPerPage: recordsPerPage.value,
  rowsNumber: 0,
  search: '',
});

/**
 * REFS
 */
const proxy: Ref<any> = ref(null);

/**
 * MOUNTED lifecycle hook
 */
onMounted(async () => {
  await onFetchTables();
});

const columns: QTableColumn[] = [
  {
    name: 'title',
    align: 'left',
    label: t('title'),
    field: 'title',
    sortable: true,
  },
  {
    name: 'external_source',
    align: 'left',
    label: t('source'),
    field: 'external_source',
    sortable: true,
  },
  {
    name: 'shortcode',
    align: 'left',
    label: t('shortcode'),
    field: 'shortcode',
    sortable: false,
  },
  {
    name: 'file_size',
    align: 'left',
    label: t('filesize'),
    field: 'file_size',
    sortable: true,
  },
  {
    name: 'created_at',
    align: 'left',
    label: t('uploaded'),
    field: 'created_at',
    sortable: true,
  },
  {
    name: 'updated_at',
    align: 'left',
    label: t('modified'),
    field: 'updated_at',
    sortable: true,
  },
  {
    name: 'actions',
    align: 'center',
    label: t('actions'),
    field: 'actions',
  },
];

/**
 * REMOVE SELECTED
 */
async function removeSelected() {
  proxy.value?.hide();
  const calls = selected.value.map((el: Table) => {
    return deleteTable(el.id);
  });

  loading.value = true;
  await Promise.all(calls);
  await onFetchTables();
  loading.value = false;

  selected.value = [];
}

/**
 * COPY TO BUFFER
 * @param shortcode
 */
async function copyToBuffer(shortcode: string) {
  copiedShortcode.value = shortcode;
  await copyToClipboard(shortcode);
}

async function onRequest(props: QTableProps) {
  const { page, rowsPerPage, sortBy, descending } = props.pagination ?? {};
  const filter = props.filter;

  if (page) {
    pagination.value.page = page;
  }

  if (rowsPerPage) {
    recordsPerPage.value = rowsPerPage;
    pagination.value.rowsPerPage = recordsPerPage.value;
  }

  pagination.value.sortBy = sortBy;
  pagination.value.descending = descending;
  pagination.value.search = filter;

  await onFetchTables();
}

/**
 * ON FETCH TABLES
 */
async function onFetchTables() {
  loading.value = true;
  await fetchTables({
    page: pagination.value.page,
    perPage: pagination.value.rowsPerPage,
    search: pagination.value.search,
    order: pagination.value.descending ? 'DESC' : 'ASC',
    orderby: pagination.value.sortBy,
  });
  loading.value = false;
  pagination.value.rowsNumber = totalPerQuery.value;
}

/**
 * SHOW UPLOADER POPUP
 */
function showUploaderPopup() {
  showUploader.value = true;
}

/**
 * ON ROW CLICK
 * @param table
 */
function onRowClick(table: Table) {
  selectedTable.value = table;
  router.push(`/table/${table.id}`);
}
</script>

<style>
.search-input {
  padding-right: 40px !important;
}
</style>
