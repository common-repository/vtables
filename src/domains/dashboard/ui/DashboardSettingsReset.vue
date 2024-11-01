<template>
  <p>
    {{ $t('messages.are_you_sure_you_want_to_reset_your_current_settings') }}
  </p>
  <q-btn
    class="full-width q-mb-sm"
    :loading="loading"
    color="negative"
    icon="restart_alt"
    outline
    @click="$emit('reset')"
  >
    <span class="q-px-sm">
      {{ $t('reset') }}
    </span>
  </q-btn>
</template>

<script setup lang="ts">
/**
 * PROPS
 */
defineProps<{
  loading: boolean;
}>();

/**
 * EMITS
 */
defineEmits(['reset']);
</script>
