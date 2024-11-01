<template>
  <q-btn
    class="q-px-sm"
    icon="tune"
    color="blue-grey"
    :title="$t('settings')"
    flat
    @click.stop="$emit('selectTable', trProps.props.row)"
  />
  <q-btn
    class="q-px-sm"
    icon="delete_outline"
    color="blue-grey"
    :title="$t('delete')"
    :disable="loading"
    flat
    @click.stop
  >
    <q-popup-proxy style="width: 300px">
      <q-banner class="text-white bg-red">
        <b>{{ $t('delete') }}</b> {{ trProps.props.row.title }}
        {{ $t('table') }}?
        <template #action>
          <div class="flex justify-between full-width">
            <q-btn flat :label="$t('cancel')" size="sm" v-close-popup />
            <q-btn
              outline
              :label="$t('confirm')"
              size="sm"
              v-close-popup
              @click="$emit('deleteTable', trProps.props.row.id)"
            />
          </div>
        </template>
      </q-banner>
    </q-popup-proxy>
  </q-btn>
</template>

<script setup lang="ts">
import { QTrProps } from 'quasar';

/**
 * PROPS
 */
defineProps<{
  loading: boolean;
  trProps: QTrProps;
}>();

/**
 * EMITS
 */
defineEmits(['selectTable', 'deleteTable']);
</script>
