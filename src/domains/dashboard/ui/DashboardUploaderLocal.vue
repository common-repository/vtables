<template>
  <q-card class="full-height" flat>
    <q-card-section class="full-height q-pa-none">
      <div class="wrap | col q-ma-none relative-position">
        <q-uploader
          class="full-width"
          :class="{ 'files-added': filesAdded }"
          :factory="(factoryFn as QUploaderFactoryFn)"
          @factory-failed="factoryFailed"
          flat
          accept=".csv, .json"
          no-thumbnails
          auto-upload
          multiple
          @added="added"
          @removed="removed"
        >
          <template #header="scope">
            <div class="row no-wrap items-center q-pa-sm q-gutter-xs">
              <q-btn
                v-if="
                  (scope.uploadedFiles.length || scope.queuedFiles.length) &&
                  !scope.isUploading
                "
                icon="done_all"
                round
                dense
                flat
                @click="doneAll(scope)"
              >
                <q-tooltip>{{ $t('done') }}</q-tooltip>
              </q-btn>
              <q-spinner v-if="scope.isUploading" class="q-uploader__spinner" />
              <div class="col">
                <div class="q-uploader__subtitle">
                  {{ scope.uploadSizeLabel }}
                </div>
              </div>
              <q-btn
                v-if="scope.canAddFiles"
                ref="pickFileBtn"
                class="invisible q-px-sm"
                icon="add_box"
                color="white"
                text-color="primary"
                dense
                @click="scope.pickFiles"
              >
                <q-uploader-add-trigger />
                <span class="q-pl-xs">{{ $t('pick_file') }}</span>
              </q-btn>
              <q-btn
                v-if="scope.isUploading"
                icon="clear"
                round
                dense
                flat
                @click="scope.abort"
              >
                <q-tooltip>{{ $t('abort_upload') }}</q-tooltip>
              </q-btn>
            </div>
          </template>
        </q-uploader>
        <div
          v-show="!filesAdded"
          class="call-to-action | full-width text-center absolute"
          @click="selectFiles"
        >
          <div class="inner">
            {{ $t('messages.drag_and_drop_csv_file_to_upload') }}
            <span class="block q-my-sm text-grey-5 text-uppercase">
              {{ $t('or') }}
            </span>
            <q-btn
              class="q-px-sm q-mb-md"
              icon="add_box"
              color="white"
              text-color="primary"
              @click="selectFiles"
              dense
              unelevated
            >
              <span class="q-pl-xs">
                {{ $t('pick_file') }}
              </span>
            </q-btn>
            <div class="text-caption text-grey-7 q-px-xl">
              {{
                $t(
                  'messages.please_ensure_you_only_use_alphanumeric_characters_dashes_underscores_spaces_and_parantheses_for_filenames'
                )
              }}
            </div>
          </div>
        </div>
      </div>
    </q-card-section>
  </q-card>
</template>

<script setup lang="ts">
import { Ref, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useQuasar, QUploader, QUploaderFactoryFn } from 'quasar';
import { parserHelper } from 'src/domains/general/helpers/parserHelper';
import { formatBytesHelper } from 'src/domains/general/helpers/sizeHelper';
import { baseUrl } from 'boot/wpapi';
import { useDashboardStore } from '../store';
import { CREATE_TABLE, INITIAL_SETTINGS } from '../store/constants';
import { isJSON } from 'src/domains/general/helpers/formatDetectHelper';
import { TableType } from '../store/models';

/**
 * EMITS
 */
const emit = defineEmits(['upload', 'close']);

/**
 * DASHBOARD STORE
 */
const dashboardStore = useDashboardStore();
const createTable = dashboardStore[CREATE_TABLE];

/**
 * CONSTANTS
 */
const { t } = useI18n();
const $q = useQuasar();
const filesAdded: Ref<number> = ref(0);
const pickFileBtn: Ref<HTMLElement | null> = ref(null);

/**
 * ADD FILE PROCESS
 * @param file
 */
function addFileProcess(file: File) {
  return new Promise((resolve, reject) => {
    const fileReader = new FileReader();
    fileReader.onload = () => resolve(fileReader.result);
    fileReader.onerror = reject;
    fileReader.readAsText(file);
  });
}

/**
 * FACTORY FUNCTION
 * @param files
 */
async function factoryFn(files: File[]) {
  const fileContent = await addFileProcess(files[0]);

  if (!fileContent) {
    $q.notify({
      type: 'negative',
      message: t('messages.something_went_wrong'),
      caption: t('messages.please_make_sure_the_file_is_valid'),
    });

    return;
  }

  const fileContentString = fileContent as string;
  const tableType: TableType = isJSON(fileContentString) ? 'json' : 'csv';
  const title = files[0].name.replace(/['"]/g, '').replace(`.${tableType}`, '');
  const source = parserHelper(fileContentString);
  const visibleColumns = source.columns?.map((el) => el.name);
  const settings = { ...INITIAL_SETTINGS };
  settings.columns.visibleColumns = visibleColumns || [];

  const { response, error } = await createTable({
    title,
    fileSize: formatBytesHelper(files[0].size),
    source,
    settings,
    tableType,
  });

  if (error) {
    $q.notify({
      type: 'negative',
      message: t('messages.something_went_wrong'),
      caption: t('messages.please_try_again_later'),
    });

    return;
  }

  emit('upload');

  return {
    url: `${baseUrl}vtables/v1/tables/${response.data?.id}`,
    method: 'GET',
  };
}

/**
 * FACTORY FAILED
 */
function factoryFailed() {
  return;
}

/**
 * SELECT FILES
 */
function selectFiles() {
  pickFileBtn.value?.click();
}

/**
 * ADDED FILES
 * @param files
 */
function added(files: readonly any[]) {
  filesAdded.value = files.length;
}

/**
 * REMOVED FILES
 */
function removed() {
  filesAdded.value -= 1;
}

function doneAll(scope: QUploader) {
  scope.removeUploadedFiles();
  scope.removeQueuedFiles();
  filesAdded.value = 0;
}
</script>

<style lang="scss">
.q-uploader {
  width: 100% !important;

  &:hover {
    .call-to-action {
      display: none;
    }
  }
}
</style>

<style lang="scss">
.wrap {
  .q-uploader {
    min-height: 360px;

    &__header {
      background-color: lightslategray;
    }

    &__list {
      background: #f5f5f5;
      border-bottom-left-radius: 4px;
      border-bottom-right-radius: 4px;
      border: 1px dotted;
      padding-left: 0;
      padding-right: 0;
    }

    &.files-added {
      .q-uploader__list {
        background: none;
        border: 0 none;
      }
    }
  }

  .call-to-action {
    top: 110px;
    z-index: 1;

    .inner {
      padding: 10px;
      cursor: pointer;
    }
  }

  .q-uploader.q-uploader--dnd + .call-to-action {
    display: none;
  }
}
</style>
