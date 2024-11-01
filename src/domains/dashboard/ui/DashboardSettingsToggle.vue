<template>
  <div class="q-mb-md q-pb-xs" :class="{ disabled: disable }">
    <div class="text-caption q-mb-xs">
      <q-icon class="q-mr-xs" name="info_outline" size="xs">
        <q-tooltip>{{ tooltip }}</q-tooltip>
      </q-icon>
      <span class="text-weight-bold">{{ label }}</span>
    </div>
    <q-btn-toggle
      v-model="toggle"
      :options="
        options
          ? options
          : [
              { label: $t('on'), value: true },
              { label: $t('off'), value: false },
            ]
      "
      :disable="disable"
      size="md"
      unelevated
      toggle-color="blue-grey-4"
      text-color="blue-grey-4"
      color="white"
      spread
      dense
    />
    <q-banner v-if="showBanner" rounded dense class="bg-grey-3 q-mt-sm">
      <div class="text-caption" style="line-height: normal">
        {{ bannerMessage }}
      </div>
    </q-banner>
  </div>
</template>

<script setup lang="ts">
import { QBtnToggleProps } from 'quasar';
import { WritableComputedRef, computed } from 'vue';

/**
 * PROPS
 */
const props = defineProps<{
  label: string;
  tooltip: string;
  modelValue: boolean | undefined;
  disable?: boolean;
  options?: QBtnToggleProps['options'];
  showBanner?: boolean;
  bannerMessage?: string;
}>();

/**
 * EMITS
 */
const emit = defineEmits(['update:model-value']);

const toggle: WritableComputedRef<boolean> = computed({
  get() {
    return props.modelValue || false;
  },
  set(newVal) {
    emit('update:model-value', newVal);
  },
});
</script>

<style scoped lang="scss">
.q-btn-toggle {
  border: 1px solid $blue-grey-4;
}
</style>
