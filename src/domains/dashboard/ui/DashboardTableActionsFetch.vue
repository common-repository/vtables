<template>
  <q-field :label="$t('source')" stack-label filled dense readonly>
    <template #control>
      <div
        class="self-center full-width no-outline"
        tabindex="0"
        style="min-width: 70px"
      >
        {{ tableType?.toLowerCase() }}
      </div>
    </template>
    <template #append>
      <q-btn
        class="q-px-sm q-ml-md"
        :loading="loading"
        icon="sync"
        color="primary"
        outline
        size="sm"
        :disable="tableType === 'woocommerce' || tableType === 'google_sheets'"
        @click="showLocalFetchDialog = true"
      >
        <span class="q-pl-xs">{{ $t('sync') }}</span>
      </q-btn>
      <q-btn
        class="q-px-sm q-ml-xs"
        icon="open_in_new"
        color="primary"
        outline
        size="sm"
        :disable="tableType === 'woocommerce' || tableType === 'google_sheets'"
        @click="open"
      >
        <span class="q-pl-xs">
          {{ $t('open') }}
        </span>
      </q-btn>
    </template>
  </q-field>

  <q-dialog v-if="showLocalFetchDialog" v-model="showLocalFetchDialog">
    <q-card style="min-width: 350px">
      <q-card-section>
        <div class="text-h6">Select file</div>
      </q-card-section>
      <q-card-section class="q-py-none">
        <q-file
          v-model="file"
          label="Pick file"
          filled
          counter
          :counter-label="counterLabelFn"
          :accept="acceptLocalFileType"
          @update:model-value="selectFile"
        >
          <template #prepend>
            <q-icon name="attach_file" />
          </template>
        </q-file>
      </q-card-section>
      <q-card-actions align="right" class="text-primary">
        <q-btn flat label="Cancel" v-close-popup />
        <q-btn
          :loading="loading"
          :label="$t('sync')"
          color="primary"
          @click="fetchLocal"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup lang="ts">
import { computed, ComputedRef, Ref, ref } from 'vue';
import { exportFile, QTableColumn, useQuasar } from 'quasar';
import { unparse } from 'papaparse';
import { useI18n } from 'vue-i18n';
import { useDashboardStore } from '../store';
import { SAVE_TABLE_CHANGES } from '../store/constants';
import { Table } from '../store/models';
import { parserHelper } from 'src/domains/general/helpers/parserHelper';
import { formatBytesHelper } from 'src/domains/general/helpers/sizeHelper';

/**
 * PROPS
 */
const props = defineProps<{
  selectedTable: Table;
}>();

/**
 * EMITS
 */
const emit = defineEmits(['fetch-selected-table']);

/**
 * DASHBOARD STORE
 */
const dashboardStore = useDashboardStore();
const saveTableChanges = dashboardStore[SAVE_TABLE_CHANGES];

/**
 * CONSTANTS
 */
const { t } = useI18n();
const $q = useQuasar();
const loading: Ref<boolean> = ref(false);
const tableType: ComputedRef<string> = computed(() => {
  if (!props.selectedTable.table_type) {
    return 'csv';
  }
  const tt: any = props.selectedTable.table_type;
  // LOCAL and REMOTE are legacy table types
  if (tt === 'local') {
    return 'csv';
  } else if (tt === 'remote') {
    return 'google_sheets';
  }

  return tt;
});
const acceptLocalFileType: ComputedRef<string> = computed(() => {
  if (
    props.selectedTable.table_type &&
    props.selectedTable.table_type === 'json'
  ) {
    return '.json';
  }
  return '.csv';
});

/**
 * OPEN LINK
 */
function open() {
  // Open external link
  if (props.selectedTable.external_source) {
    const windowObj: any = window;
    windowObj.open(props.selectedTable.external_source, '_blank').focus();
    return;
  }

  // Download local file
  const {
    title,
    source: { columns, rows },
  } = props.selectedTable;

  let name = `${title}.csv`;
  let content = '';

  if (tableType.value === 'csv') {
    const csvContent = unparse(rows);
    const headers = columns.map((el: QTableColumn) => el.label);
    const csvLines = csvContent.split('\n');
    csvLines[0] = headers;
    content = csvLines.join('\n');
  }

  if (tableType.value === 'json') {
    name = `${title}.json`;
    content = JSON.stringify(rows);
  }

  const status = exportFile(name, content);

  if (!status) {
    $q.notify({
      type: 'negative',
      message: t('failed'),
    });
  }
}

/**
 * FETCH LOCAL
 */
const showLocalFetchDialog: Ref<boolean> = ref(false);
const file: Ref<File | null> = ref(null);

function selectFile(value: File) {
  file.value = value;
}

function counterLabelFn({ totalSize }: { totalSize: string }) {
  return `${totalSize}`;
}

function addFileProcess(file: File) {
  return new Promise((resolve, reject) => {
    const fileReader = new FileReader();
    fileReader.onload = () => resolve(fileReader.result);
    fileReader.onerror = reject;
    fileReader.readAsText(file);
  });
}

async function fetchLocal() {
  if (!file.value) {
    return;
  }

  const fileContent = await addFileProcess(file.value);

  if (!fileContent) {
    $q.notify({
      type: 'negative',
      message: t('messages.couldnt_parse_file_content'),
      caption: t('messages.please_make_sure_the_file_is_valid'),
    });

    return;
  }

  const fileContentString = fileContent as string;
  const source = parserHelper(fileContentString);
  const { id, title } = props.selectedTable;

  // Check if columns matched
  const currentColumns = props.selectedTable.source.columns;
  const newFileColumns = source.columns;
  const columnsMatched =
    newFileColumns.every(
      (el, i) => JSON.stringify(el) === JSON.stringify(currentColumns[i])
    ) && newFileColumns.length === currentColumns.length;

  if (!columnsMatched) {
    $q.notify({
      type: 'negative',
      message: t('messages.column_mismatch'),
      caption: t('messages.please_make_sure_the_columns_align_'),
    });

    return;
  }

  loading.value = true;

  const { error } = await saveTableChanges({
    id: id,
    title: title,
    source: source,
    fileSize: formatBytesHelper(file.value.size),
  });

  loading.value = false;
  showLocalFetchDialog.value = false;

  if (error) {
    $q.notify({
      type: 'negative',
      message: t('messages.unable_to_save_changes'),
    });

    return;
  }

  emit('fetch-selected-table');
}
</script>
