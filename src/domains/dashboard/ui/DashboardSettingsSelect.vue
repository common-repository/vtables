<template>
  <div class="q-mb-md q-pb-xs q-mt-sm" :class="{ disabled: disable }">
    <p class="text-caption q-mb-xs">
      <q-icon class="q-mr-xs" name="info_outline" size="xs">
        <q-tooltip>{{ tooltip }}</q-tooltip>
      </q-icon>
      <span class="text-weight-bold">{{ label }}</span>
    </p>
    <q-select
      v-model="selected"
      class="full-width"
      :options="options"
      :emit-value="emitValue"
      :map-options="mapOptions"
      :disable="disable"
      outlined
      dense
      options-dense
    >
      <slot />
    </q-select>
    <q-banner
      v-if="showBanner"
      rounded
      dense
      class="bg-grey-3 q-mt-sm"
      style="text-transform: none"
    >
      <div class="text-caption" style="line-height: normal">
        {{ bannerMessage }}
      </div>
    </q-banner>
  </div>
</template>

<script setup lang="ts">
import { WritableComputedRef, computed } from 'vue';

/**
 * PROPS
 */
const props = defineProps<{
  options: Record<string | number | symbol, unknown>[] | string[];
  modelValue: any;
  label: string;
  tooltip: string;
  emitValue?: boolean;
  mapOptions?: boolean;
  disable?: boolean;
  showBanner?: boolean;
  bannerMessage?: string;
}>();

/**
 * EMITS
 */
const emit = defineEmits(['update:model-value']);

const selected: WritableComputedRef<any> = computed({
  get() {
    return props.modelValue;
  },
  set(newVal) {
    emit('update:model-value', newVal);
  },
});
</script>
