import { defineStore } from 'pinia';
import { DASHBOARD_STORE } from './constants';
import actions from './actions';
import state from './state';

export const useDashboardStore = defineStore(DASHBOARD_STORE, () => {
  return { ...actions, ...state };
});
