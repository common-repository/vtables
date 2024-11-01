import { QTableColumn } from 'quasar';

export default function columnsHelper(data: string[], isSortable = false) {
  if (!data) {
    return [];
  }

  const columns: QTableColumn[] = data.map((el, i) => {
    const val = el.replace(/['"]/g, '');
    const fieldValue = val
      .trim()
      .toLowerCase()
      .replace(/[^A-Z0-9]+/gi, '_');
    const name = el ? fieldValue : `_empty_col_${i}`;
    const field = el ? fieldValue : `_empty_col_${i}`;
    const label = el.trim().replace(/['"_]/g, '');

    return {
      name,
      align: 'left',
      label,
      field,
      sortable: isSortable,
    };
  });

  return columns;
}
