<template>
  <q-dialog v-if="show" v-model="show">
    <q-card style="min-width: 350px">
      <q-card-section>
        <div class="text-h6">Change Title</div>
      </q-card-section>
      <q-card-section class="q-py-none">
        <q-input
          v-model="newTableTitle"
          outlined
          dense
          autofocus
          :error="v$.newTableTitle.$error"
          :error-message="(v$.newTableTitle.$errors[0]?.$message as string)"
          @keyup.enter="update"
        />
      </q-card-section>
      <q-card-actions align="right" class="text-primary">
        <q-btn flat label="Cancel" v-close-popup />
        <q-btn
          :loading="loading"
          label="Save"
          color="primary"
          @click="update"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup lang="ts">
import { Ref, WritableComputedRef, computed, ref } from 'vue';
import { required } from '@vuelidate/validators';
import useVuelidate from '@vuelidate/core';

/**
 * PROPS
 */
const props = defineProps<{
  modelValue: boolean;
  title: string;
  loading: boolean;
}>();

/**
 * EMITS
 */
const emit = defineEmits(['update', 'update:model-value']);

/**
 * CONSTANTS
 */
const newTableTitle: Ref<string> = ref(props.title);
const show: WritableComputedRef<boolean> = computed({
  get() {
    return props.modelValue;
  },
  set(newVal: boolean) {
    emit('update:model-value', newVal);
  },
});

/**
 * VALIDATION
 */
const validationRules = {
  newTableTitle: {
    required,
  },
};
const v$ = useVuelidate(validationRules, { newTableTitle });

/**
 * UPDATE
 */
function update() {
  v$.value.$validate();

  if (v$.value.$error) {
    return;
  }

  emit('update', newTableTitle.value);
}
</script>
