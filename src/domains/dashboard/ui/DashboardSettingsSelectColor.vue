<template>
  <div class="q-mb-md q-pb-xs" :class="{ disabled: disable }">
    <p class="text-caption q-mb-xs">
      <q-icon class="q-mr-xs" name="info_outline" size="xs">
        <q-tooltip>{{ tooltip }}</q-tooltip>
      </q-icon>
      <span class="text-weight-bold">{{ label }}</span>
    </p>
    <q-select
      v-model="color"
      class="full-width"
      :options="colors"
      outlined
      dense
      options-dense
      display-value=""
      :disable="disable"
    >
      <template #selected-item>
        <q-chip
          v-if="color"
          class="full-width text-caption"
          :color="color"
          size="xs"
        />
        <div
          v-else
          class="text-body2 ellipsis"
          style="color: rgba(0, 0, 0, 0.6)"
        >
          {{ $t('select_color') }}
        </div>
      </template>
      <template #option="scope">
        <q-item v-bind="scope.itemProps">
          <q-item-section>
            <q-chip class="q-ma-none" :color="scope.opt" size="sm" />
          </q-item-section>
        </q-item>
      </template>
      <template v-if="color" #append>
        <q-icon
          name="cancel"
          class="cursor-pointer"
          color="grey-5"
          @click.stop.prevent="emit('select-color', '')"
        />
      </template>
    </q-select>
  </div>
</template>

<script setup lang="ts">
import { WritableComputedRef, computed } from 'vue';

/**
 * PROPS
 */
const props = defineProps<{
  colors: string[];
  selectedColor: string | undefined;
  label: string;
  tooltip: string;
  disable?: boolean;
}>();

/**
 * EMITS
 */
const emit = defineEmits(['select-color']);

const color: WritableComputedRef<string> = computed({
  get() {
    return props.selectedColor || '';
  },
  set(newVal) {
    emit('select-color', newVal);
  },
});
</script>

<style scoped lang="scss">
.q-chip {
  border: 2px solid $grey-4;
}
</style>
