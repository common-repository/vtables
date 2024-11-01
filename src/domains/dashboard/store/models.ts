import { QTableColumn, QTableProps } from 'quasar';

export interface MediaDetails {
  filesize: number;
}

export interface SettingsGeneral {
  backgroundColor: string;
  bordered: boolean;
  dark: boolean;
  dense: boolean;
  flat: boolean;
  fullscreen: boolean;
  grid: boolean;
  separator: 'horizontal' | 'vertical' | 'cell' | 'none' | undefined;
  square: boolean;
  textColor: string;
}

export interface SettingsBottom {
  backgroundColor: string;
  hidePagination: boolean;
  initial: QTableProps['pagination'];
  rowsPerPageLabel: string;
  textColor: string;
}

export interface SettingsColumns {
  columnsMaxWidth: Record<string, number>[];
  stickyFirstColumn: boolean;
  stickyFirstColumnBgColor: string;
  stickyFirstColumnTextColor: string;
  stickyLastColumn: boolean;
  stickyLastColumnBgColor: string;
  stickyLastColumnTextColor: string;
  visibleColumns: string[];
}

export interface SettingsContent {
  buttonText: string;
  buttonBgColor: string;
  buttonTextColor: string;
  imageHeight: number;
  imageWidth: number;
  previewMode: boolean;
  turnLinksToButtons: boolean;
  turnLinksToImages: boolean;
  verticalAlignment: 'bottom' | 'middle' | 'top';
}

export interface SettingsCustomCss {
  cardClass: string;
  tableClass: string;
  tableHeaderClass: string;
  titleClass: string;
}

export interface SettingsDimensions {
  tableHeight: number | string | null;
  tableMaxWidth: string | null;
}

export interface SettingsTop {
  hideHeader: boolean;
  headerBackgroundColor: string;
  headerTextColor: string;
  hideTitle: boolean;
  stickyHeader: boolean;
  textColor: string;
  topBackgroundColor: string;
}

export interface SettingsSearch {
  color: string;
  searchOn: boolean;
  filter: string;
  searchInputStyle: 'borderless' | 'filled' | 'outlined' | 'standard';
}

export interface SettingsSorting {
  sortingOn: boolean;
  binaryStateSort: boolean;
  columnSortOrder: 'ad' | 'da';
}

export interface SettingsVirtualScroll {
  virtualScrollOn: boolean;
  virtualScrollSliceSize: number;
  virtualScrollSliceRatioBefore: number;
  virtualScrollSliceRatioAfter: number;
  virtualScrollStickySizeStart: number;
}

export interface Settings {
  general: SettingsGeneral;
  bottom: SettingsBottom;
  content: SettingsContent;
  columns: SettingsColumns;
  customCss: SettingsCustomCss;
  dimensions: SettingsDimensions;
  search: SettingsSearch;
  sorting: SettingsSorting;
  top: SettingsTop;
  virtualScroll: SettingsVirtualScroll;
}

export interface Table {
  id: string;
  title: string;
  shortcode: string;
  file_size: string;
  settings: Settings;
  created_at: string | Date;
  updated_at: string | Date;
  source: Record<string | number | symbol, any>;
  external_source: string | null;
  table_type?: TableType;
  woocommerce_categories?: [];
}

export interface Pagination {
  sortBy: string | undefined;
  descending: boolean | undefined;
  page: number;
  rowsPerPage: number;
  rowsNumber: number;
  search: string;
}

export interface CellMerged {
  mainCell: number;
  cells: number[];
}

export interface Row {
  [key: string | number | symbol]: any;
}

export interface TopRow {
  cols: QTableColumn[];
}

export type TableType = 'csv' | 'json' | 'google_sheets' | 'woocommerce';
