import { parse, ParseResult } from 'papaparse';
import { Row } from 'src/domains/dashboard/store/models';
import columnsHelper from './columnsHelper';
import { isJSON } from './formatDetectHelper';

export function parserHelper(content: string) {
  // JSON
  if (isJSON(content)) {
    const jsonContent = JSON.parse(content);
    const headers = Object.keys(jsonContent[0]);
    const columns = columnsHelper(headers);
    const rows = jsonContent;

    return { columns, rows };
  }

  // CSV
  const parsedFileContent: ParseResult<string[]> = parse(content);
  const headers = parsedFileContent.data.shift();

  if (!headers?.length || parsedFileContent.data?.length === 0) {
    return {
      columns: [],
      rows: [],
    };
  }

  const columns = columnsHelper(headers);

  const filteredRows = parsedFileContent.data.filter((el) => {
    const isRowEmpty = el.every((item) => !item);

    if (!isRowEmpty) {
      return el;
    }
  });

  const rows: Row[] = filteredRows.map((el) => {
    let obj = {};

    columns.forEach((col, index) => {
      let value: string | number = el[index];
      if (value?.includes('\n')) {
        value = value.replace(/\n\n|\\n\\n/g, '');
      }
      if (!isNaN(Number(value)) && value !== '') {
        value = Number(value);
      }
      obj = {
        ...obj,
        [col.name]: value,
      };
    });

    return obj;
  });

  return {
    rows,
    columns,
  };
}
