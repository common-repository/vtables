<template>
  <div class="woo-uploader">
    <DashboardUploaderBanner />

    <q-input v-model="tableName" filled :label="$t('table_name')" />

    <br />

    <q-select
      v-model="selectedOption"
      class="q-mb-md"
      filled
      :options="[]"
      :label="$t('i_want_to')"
      disable
    />

    <q-btn
      :label="$t('import')"
      class="full-width"
      color="primary"
      icon="cloud_download"
      disable
    />
  </div>
</template>

<script setup lang="ts">
import { Ref, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import DashboardUploaderBanner from './DashboardUploaderBanner.vue';

/**
 * CONSTANTS
 */
const { t } = useI18n();
const tableName: Ref<string> = ref('');
const optionsValues: Record<string, string>[] = [
  { label: t('import_all_products'), value: 'all_products' },
  {
    label: t('import_products_from_categories'),
    value: 'specific_categories',
  },
];
const selectedOption: Ref<Record<string, string>> = ref(optionsValues[0]);
</script>

<style lang="scss">
.woo-uploader {
  .q-field__bottom {
    padding-left: 0;
    padding-top: 3px;
    padding-bottom: 2px;
  }
}
</style>
