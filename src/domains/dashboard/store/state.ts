import { ComputedRef, Ref, computed, ref } from 'vue';
import { Table } from './models';

const loading: Ref<boolean> = ref(false);
const tables: Ref<Table[]> = ref([]);
const totalPerQuery: Ref<number> = ref(0);
const totalInDb: Ref<number> = ref(0);
const filterIds: Ref<string[]> = ref([]);
const selectedTable: Ref<Table | null> = ref(null);
const copiedShortcode: Ref<string> = ref('');
const getUserId: ComputedRef<string> = computed(() => {
  const {
    userSettings: { uid },
  } = window as any;
  return uid;
});

export default {
  tables,
  totalPerQuery,
  totalInDb,
  filterIds,
  getUserId,
  loading,
  copiedShortcode,
  selectedTable,
};
