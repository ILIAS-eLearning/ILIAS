/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Controller (handles editor initialisation process)
 */
export default class Controller {

  /**
   * @type {UI}
   */
  //ui;

  constructor(ui) {
    this.ui = ui;
  }

  /**
   * Init editor
   */
  init(after_init) {
    this.ui.init(after_init);
  }

  /**
   * Re-Init after page being fully refreshed via ajax (legacy call from page_editing.js)
   */
  reInit() {
    this.ui.reInit();
  }

}