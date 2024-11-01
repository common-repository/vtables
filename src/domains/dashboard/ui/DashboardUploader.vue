<template>
  <q-dialog v-model="showModal">
    <q-card class="uploader-popup">
      <q-card-section class="bg-grey-3">
        <q-btn class="absolute-right" flat icon="close" v-close-popup />
        <h6 class="text-h6 q-ma-none">{{ $t('upload') }}</h6>
      </q-card-section>

      <div class="q-gutter-y-md">
        <q-card>
          <q-splitter :model-value="20" style="height: 400px">
            <template #before>
              <q-tabs
                v-model="activeTab"
                dense
                class="text-grey bg-grey-2 text-left"
                active-color="primary"
                indicator-color="primary"
                vertical
              >
                <q-tab name="csv_json" label="CSV / JSON" />
                <q-tab name="google_sheets" label="Google Sheets" />
                <q-tab name="woocommerce" label="Woo Products" />
              </q-tabs>
            </template>
            <template #after>
              <q-tab-panels v-model="activeTab" class="full-height" vertical>
                <q-tab-panel name="csv_json">
                  <DashboardUploaderLocal
                    @upload="$emit('upload')"
                    @close="$emit('close')"
                  />
                </q-tab-panel>
                <q-tab-panel name="google_sheets">
                  <DashboardUploaderRemote />
                </q-tab-panel>
                <q-tab-panel name="woocommerce">
                  <DashboardUploaderWoo />
                </q-tab-panel>
              </q-tab-panels>
            </template>
          </q-splitter>
        </q-card>
      </div>
    </q-card>
  </q-dialog>
</template>

<script setup lang="ts">
import { Ref, ref, WritableComputedRef, computed } from 'vue';
import DashboardUploaderLocal from './DashboardUploaderLocal.vue';
import DashboardUploaderRemote from './DashboardUploaderRemote.vue';
import DashboardUploaderWoo from './DashboardUploaderWoo.vue';

/**
 * PROPS
 */
const props = defineProps<{
  show: boolean;
}>();

/**
 * EMITS
 */
const emit = defineEmits(['close', 'upload']);

/**
 * CONSTANTS
 */
const activeTab: Ref<string> = ref('csv_json');
const showModal: WritableComputedRef<boolean> = computed({
  get() {
    return props.show;
  },
  set() {
    close();
  },
});

/**
 * CLOSE
 */
function close() {
  emit('close');
}
</script>

<style lang="scss">
.uploader-popup {
  min-width: 700px;
  .q-tab {
    justify-content: flex-start;
  }
  .q-tab__label {
    font-size: 12px;
    padding-left: 7px;
  }
}
</style>
