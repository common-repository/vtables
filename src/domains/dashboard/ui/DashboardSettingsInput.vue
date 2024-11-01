<template>
  <div :class="{ disabled: disable }">
    <p class="text-caption q-mb-xs">
      <q-icon class="q-mr-xs" name="info_outline" size="xs">
        <q-tooltip>{{ tooltip }}</q-tooltip>
      </q-icon>
      <span class="text-weight-bold">{{ label }}</span>
    </p>
    <q-input
      v-if="type === 'number'"
      v-model.number="text"
      class="full-width"
      :placeholder="label"
      :disable="disable"
      :error="v$.text.$error"
      :error-message="v$.text.$error ? v$.text.$errors[0].$message as string : ''"
      outlined
      dense
    >
      <template #append>
        <slot name="append" />
      </template>
      <template v-if="v$.text.$error" #error>
        {{ v$.text.$errors[0].$message }}
      </template>
    </q-input>
    <q-input
      v-else
      v-model="text"
      class="full-width q-mb-md q-pb-xs"
      :placeholder="label"
      :disable="disable"
      outlined
      dense
    >
      <template #append>
        <slot name="append" />
      </template>
    </q-input>
  </div>
</template>

<script setup lang="ts">
import { WritableComputedRef, computed } from 'vue';
import { QInputProps } from 'quasar';
import { useVuelidate } from '@vuelidate/core';
import { numeric } from '@vuelidate/validators';

/**
 * PROPS
 */
const props = defineProps<{
  label: string;
  tooltip: string;
  modelValue: any;
  disable?: boolean;
  type?: QInputProps['type'];
}>();

/**
 * EMITS
 */
const emit = defineEmits(['update:model-value']);

const text: WritableComputedRef<string | number | null> = computed({
  get() {
    return props.modelValue;
  },
  set(newVal) {
    emit('update:model-value', newVal);
  },
});

/**
 * VALIDATION
 */
const rules = computed(() => ({
  text: {
    numeric,
    $autoDirty: true,
  },
}));
const v$ = useVuelidate(rules, { text });
</script>

<style>
.q-field__bottom {
  padding-top: 2px;
  padding-bottom: 2px;
}
</style>
