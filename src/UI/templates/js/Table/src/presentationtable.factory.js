import PresentationTable from './presentationtable.class';

export default class PresentationTableFactory {
  /**
   * @type {Array<string, PresentationTable>}
   */
  #instances = [];

  /**
   * @param {string} tableId
   */
  init(tableId) {
    if (this.#instances[tableId] !== undefined) {
      throw new Error(`PresentationTable with input-id '${tableId}' has already been initialized.`);
    }
    this.#instances[tableId] = new PresentationTable(tableId);
  }

  /**
   * @param {string} tableId
   * @return {PresentationTable|null}
   */
  get(tableId) {
    return this.#instances[tableId] ?? null;
  }
}
