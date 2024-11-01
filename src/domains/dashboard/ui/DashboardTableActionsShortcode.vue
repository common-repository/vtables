<template>
  <q-field :label="$t('shortcode')" stack-label filled dense readonly>
    <template #control>
      <div class="self-center full-width no-outline" tabindex="0">
        {{ shortcode }}
      </div>
    </template>
    <template #append>
      <q-btn
        class="q-px-sm q-ml-md"
        :icon="copied ? 'check' : 'content_copy'"
        color="primary"
        outline
        size="sm"
        @click="copy(shortcode)"
      >
        <span class="q-pl-xs">
          {{ $t('copy') }}
        </span>
      </q-btn>
    </template>
  </q-field>
</template>

<script setup lang="ts">
import { useClipboard } from '@vueuse/core';

/**
 * PROPS
 */
defineProps<{
  shortcode: string;
}>();

const { copy, copied } = useClipboard();
</script>
