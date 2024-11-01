<template>
  <div>
    <DashboardUploaderBanner />

    <div class="google-sheet-importer row q-mb-md">
      <div class="col">
        <q-toolbar
          class="bg-primary text-white rounded-borders justify-end q-mb-none"
        >
          <q-btn
            v-if="allFilesUploaded"
            class="q-px-sm"
            icon="done"
            :label="$t('done')"
            dense
            color="white"
            text-color="primary"
          />
          <q-btn
            v-else
            class="q-px-sm"
            icon="cloud_download"
            :label="$t('import')"
            dense
            color="white"
            text-color="primary"
          />
        </q-toolbar>
        <div
          class="row q-mb-none q-pb-sm items-center text-weight-bold bg-grey-2"
        >
          <div class="col-4 q-pl-sm">
            {{ $t('title') }}
          </div>
          <div class="col q-pl-md">URL</div>
          <div class="col-1 text-right">
            <q-btn icon="add_circle_outline" flat round />
          </div>
        </div>
        <div
          v-for="(remoteFile, idx) in remoteFilesToUpload.collection"
          :key="idx"
          class="url-input | row q-gutter-sm bg-white q-mb-none"
        >
          <div class="col-4">
            <q-input
              v-model="remoteFile.title"
              :bg-color="remoteFile.uploaded ? 'green-2' : ''"
              :disable="remoteFile.uploaded"
              placeholder="Title"
              type="text"
              outlined
              dense
            />
          </div>
          <div class="col flex items-start no-wrap">
            <q-input
              v-model.trim="remoteFile.url"
              class="full-width"
              :bg-color="remoteFile.uploaded ? 'green-2' : ''"
              :disable="remoteFile.uploaded"
              :placeholder="$t('messages.paste_a_google_sheet_url')"
              type="text"
              outlined
              dense
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ComputedRef, computed, reactive } from 'vue';
import DashboardUploaderBanner from './DashboardUploaderBanner.vue';

export interface RemoteFile {
  title: string;
  url: string;
  uploaded: boolean;
  error: {
    urlNotRechable?: boolean;
    nameExists?: boolean;
  } | null;
}

/**
 * CONSTANTS
 */
const emptyItem: RemoteFile = {
  title: '',
  url: '',
  uploaded: false,
  error: null,
};
const remoteFilesToUpload: { collection: RemoteFile[] } = reactive({
  collection: [emptyItem],
});
const allFilesUploaded: ComputedRef<boolean> = computed(() =>
  remoteFilesToUpload.collection.every((el) => el.uploaded)
);
</script>

<style lang="scss">
.google-sheet-importer {
  .q-toolbar {
    background-color: lightslategray !important;
    border-bottom-left-radius: inherit;
    border-bottom-right-radius: inherit;
  }
  .q-field__bottom {
    padding-top: 3px;
  }
}
</style>
