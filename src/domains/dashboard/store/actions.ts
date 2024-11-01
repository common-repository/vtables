import { api } from 'boot/services';
import { merge } from 'lodash';
import {
  CREATE_TABLE,
  DELETE_TABLE,
  FETCH_TABLES,
  SAVE_TABLE_CHANGES,
  SAVE_TABLE_SETTINGS,
  FETCH_TABLE,
  INITIAL_SETTINGS,
} from './constants';
import dashboardState from './state';
import { Settings, TableType, type Table } from './models';
import { QTableColumn } from 'quasar';

export default {
  async [FETCH_TABLES]({
    search,
    perPage,
    page,
    order,
    orderby,
  }: {
    search: string;
    perPage: number;
    page: number;
    order?: 'ASC' | 'DESC';
    orderby?: string;
  }) {
    const { response, error } = await api.fetchTables({
      page,
      perPage,
      search,
      order,
      orderby,
    });

    if (error) {
      return { error };
    }

    const tables = response.data?.tables.map((el: Table) => {
      const settingsInStore = JSON.stringify(INITIAL_SETTINGS);
      const initialSettings = JSON.parse(settingsInStore);
      const settings = merge(initialSettings, el.settings);

      return {
        ...el,
        shortcode: `[vtables id=${el.id}]`,
        settings: { ...settings },
      };
    });

    dashboardState.tables.value = tables;
    dashboardState.totalPerQuery.value = response.data?.total;

    if (!search) {
      dashboardState.totalInDb.value = response.data?.total;
    }

    return { tables };
  },

  async [FETCH_TABLE](id: string) {
    const { response, error } = await api.fetchTable(id);

    if (error) {
      return { error };
    }

    const { source, settings } = response.data;

    const newColumns = source.columns.reduce(
      (acc: string[], el: QTableColumn) => {
        if (dashboardState.selectedTable.value) {
          const isColumnPresent =
            dashboardState.selectedTable.value.source.columns.find(
              (col: QTableColumn) => col.name === el.name
            );

          if (isColumnPresent) {
            return acc;
          }

          const columnNotIncludedInVisibleColumns =
            !dashboardState.selectedTable.value?.settings.columns.visibleColumns.includes(
              el.name
            );

          if (columnNotIncludedInVisibleColumns) {
            acc.push(el.name);
          }
        }

        return acc;
      },
      []
    );

    if (newColumns.length) {
      settings.columns.visibleColumns = [
        ...settings.columns.visibleColumns,
        ...newColumns,
      ];
    }

    const settingsInStore = JSON.stringify(INITIAL_SETTINGS);
    const initialSettings = JSON.parse(settingsInStore);
    const mergedSettings = merge(initialSettings, settings);

    response.data = {
      ...response.data,
      shortcode: `[vtables id=${response.data.id}]`,
      settings: { ...mergedSettings },
    };

    dashboardState.selectedTable.value = response.data;

    return { response };
  },

  async [SAVE_TABLE_CHANGES](payload: {
    id: string;
    title: string;
    source?: Record<string, unknown>;
    fileSize?: string;
  }) {
    const { response, error } = await api.saveTableChanges(payload);

    if (error) {
      return { error };
    }

    if (dashboardState.selectedTable.value) {
      const tableId = dashboardState.selectedTable.value.id;

      const tableToUpdate = dashboardState.tables.value.find(
        (el: Table) => el.id === tableId
      );

      if (tableToUpdate && response.success) {
        tableToUpdate.title = response.data.title;
        tableToUpdate.updated_at = response.data.updated_at;

        if (payload.source) {
          tableToUpdate.file_size = response.data.file_size;
        }
      }
    }

    return { response };
  },

  async [SAVE_TABLE_SETTINGS](payload: {
    id: string;
    settings: Record<string, unknown>;
  }) {
    const { response, error } = await api.saveTableSettings(payload);

    if (error) {
      return { error };
    }

    if (dashboardState.selectedTable.value) {
      const tableId = dashboardState.selectedTable.value.id;

      const tableToUpdate = dashboardState.tables.value.find(
        (el: Table) => el.id === tableId
      );

      if (tableToUpdate && response.success) {
        tableToUpdate.updated_at = response.data.updated_at;
      }
    }

    return { response };
  },

  async [DELETE_TABLE](id: string) {
    const { error } = await api.deleteTable(id);

    if (error) {
      return { error };
    }

    dashboardState.tables.value = dashboardState.tables.value.filter(
      (table: Table) => Number(table.id) !== Number(id)
    );

    if (!dashboardState.tables.value.length) {
      dashboardState.totalInDb.value = 0;
    }
  },

  async [CREATE_TABLE](payload: {
    title: string;
    source: Record<string | number | symbol, unknown>;
    fileSize: string;
    externalSource?: string;
    settings: Settings;
    tableType?: TableType;
    woocommerceCategories?: number[];
  }) {
    const { response, error } = await api.createTable(payload);

    if (error) {
      return { error };
    }

    return { response };
  },
};
