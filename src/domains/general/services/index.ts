import { wpapi } from 'boot/wpapi';
import { promiseHelper } from '../helpers/promiseHelper';
import { Settings, TableType } from 'src/domains/dashboard/store/models';

export const api = {
  async fetchTables({
    page,
    perPage,
    search,
    order,
    orderby,
  }: {
    page: number;
    perPage: number;
    search: string;
    order?: 'ASC' | 'DESC';
    orderby?: string;
  }) {
    return await promiseHelper(
      wpapi
        .vtables()
        .search(search)
        .page(page)
        .perPage(perPage)
        .orderby(orderby)
        .order(order)
    );
  },

  async fetchTable(id: string) {
    return await promiseHelper(wpapi.vtables().id(id));
  },

  async deleteTable(id: string) {
    return await promiseHelper(wpapi.vtables().delete({ id }));
  },

  async createTable({
    title,
    source,
    fileSize,
    externalSource,
    settings,
    tableType,
    woocommerceCategories,
  }: {
    title: string;
    source: Record<string | number | symbol, unknown>;
    fileSize: string;
    externalSource?: string;
    settings: Settings;
    tableType?: TableType;
    woocommerceCategories?: number[];
  }) {
    return await promiseHelper(
      wpapi.vtables().create({
        source,
        title,
        file_size: fileSize,
        external_source: externalSource,
        settings,
        table_type: tableType,
        woocommerce_categories: woocommerceCategories,
      })
    );
  },

  async saveTableChanges(payload: {
    id: string;
    title: string;
    source?: Record<string | number | symbol, unknown>;
  }) {
    return await promiseHelper(wpapi.vtables_source().update(payload));
  },

  async saveTableSettings(payload: {
    id: string;
    settings: Record<string | number | symbol, unknown>;
  }) {
    return await promiseHelper(wpapi.vtables_settings().update(payload));
  },
};
