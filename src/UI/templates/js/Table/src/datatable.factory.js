import DataTable from './datatable.class';

export default class DataTableFactory {
  /**
    * @type {jQuery}
    */
  #jquery;

  /**
   * @type {Params}
   */
  #params;

  /**
     * @type {Array<string, DataTable>}
     */
  #instances = [];

  /**
    * @param {jQuery} jquery
    * @param {Params} params
    */
  constructor(jquery, params) {
    this.#jquery = jquery;
    this.#params = params;
  }

  /**
     * @param {string} tableId
     * @param {string} typeURL
     * @param {string} typeSignal
     * @param {string} optOptions
     * @param {string} optId
     * @return {void}
     * @throws {Error} if the input was already initialized.
     */
  init(tableId, typeURL, typeSignal, optOptions, optId) {
    if (this.#instances[tableId] !== undefined) {
      throw new Error(`DataTable with input-id '${tableId}' has already been initialized.`);
    }

    this.#instances[tableId] = new DataTable(
      this.#jquery,
      this.#params,
      typeURL,
      typeSignal,
      optOptions,
      optId,
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
