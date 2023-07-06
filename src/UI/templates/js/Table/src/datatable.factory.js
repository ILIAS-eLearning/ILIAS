import DataTable from './datatable.class';

export default class DataTableFactory {
  /**
    * @type {jQuery}
    */
  #jquery;

  /**
   * @type {Array<string, DataTable>}
   */
  #instances = [];

  /**
   * @param {jQuery} jquery
   */
  constructor(jquery) {
    this.#jquery = jquery;
  }

  /**
   * @param {string} tableId
   * @param {string} optActionId
   * @param {string} optRowId
   * @return {void}
   * @throws {Error} if the table was already initialized.
   */
  init(tableId, optActionId, optRowId) {
    if (this.#instances[tableId] !== undefined) {
      throw new Error(`DataTable with id '${tableId}' has already been initialized.`);
    }

    this.#instances[tableId] = new DataTable(
      this.#jquery,
      optActionId,
      optRowId,
      tableId,
    );
  }

  /**
   * @param {string} tableId
   * @return {DataTable|null}
   */
  get(tableId) {
    return this.#instances[tableId] ?? null;
  }
}
