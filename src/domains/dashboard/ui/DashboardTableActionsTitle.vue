<template>
  <q-field :label="$t('title')" stack-label filled dense readonly>
    <template #control>
      <div class="self-center full-width no-outline" tabindex="0">
        <span
          class="inline-block ellipsis"
          style="min-width: 60px; max-width: 100px"
        >
          {{ title }}
        </span>
      </div>
    </template>
    <template #append>
      <q-btn
        class="q-px-sm q-ml-md"
        icon="edit"
        color="primary"
        outline
        size="sm"
        @click="$emit('change-title')"
      >
        <span class="q-pl-xs">
          {{ $t('edit') }}
        </span>
      </q-btn>
    </template>
  </q-field>
</template>

<script setup lang="ts">
/**
 * PROPS
 */
defineProps<{
  title: string;
}>();

/**
 * EMITS
 */
defineEmits(['change-title']);
</script>
