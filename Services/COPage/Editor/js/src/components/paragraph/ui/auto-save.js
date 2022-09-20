
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Auto-save for paragraphs
 */
export default class AutoSave {

  //AUTO_SAVE_ELEMENT_ID = "copg-auto-save";

  /**
   *
   * @type {boolean}
   */
  //debug = false;

  /**
   *
   * @type {boolean}
   */
  //auto_save_running = false;

  /**
   *
   * @type {number}
   */
  //auto_save_ts = 0;

  /**
   *
   * @type {number}
   */
  //autoSaveInterval = 0;

  /**
   *
   * @type {Function}
   */
  //onAutoSave = null;

  /**
   */
  constructor() {
    this.AUTO_SAVE_ELEMENT_ID = "copg-auto-save";
    this.debug = false;
    this.auto_save_running = false;
    this.auto_save_ts = 0;
    this.autoSaveInterval = 0;
    this.onAutoSave = [];
  }

  setInterval(sec) {
    this.autoSaveInterval = sec;
  }

  addOnAutoSave(onAutoSave) {
    this.onAutoSave.push(onAutoSave);
  }


  /**
   * @param message
   */
  log(message) {
    if (this.debug) {
      console.log(message);
    }
  }


  getCurrentTimestamp() {
    return Math.floor(Date.now() / 1000);
  }

  resetAutoSave() {
    this.log("AutoSave: reset");
    this.auto_save_ts = 0;
    this.auto_save_running = false;
  }

  handleAutoSaveKeyPressed() {
    this.log("AutoSave: handleKeyPressed");
    if (this.auto_save_ts === 0) {
      this.auto_save_ts = this.getCurrentTimestamp();
      this.startAutoSave();
    }
  }

  startAutoSave() {
    this.log("AutoSave: start, interval: " + this.autoSaveInterval);
    if (this.autoSaveInterval > 0) {
      this.auto_save_running = true;
      this.updateAutoSave();
    }
  }

  calculateAutoSaveTimeSpent() {
    return this.getCurrentTimestamp() - this.auto_save_ts;
  }

  calculateAutoSaveSecondsToNextSaving() {
    return this.autoSaveInterval - this.calculateAutoSaveTimeSpent();
  }

  updateAutoSave() {
    this.log("AutoSave: update " + this.auto_save_running);
    if (this.auto_save_running) {

      if (this.debug) {
        this.displayAutoSave(this.calculateAutoSaveSecondsToNextSaving());
      }
      if (this.calculateAutoSaveSecondsToNextSaving() <= 0) {
        this.autoSave();
      }

      window.setTimeout(() => {
        this.updateAutoSave();
      }, 1000);
    }
  }

  stopAutoSave() {
    this.log("AutoSave: stop");
    this.resetAutoSave();
  }

  displayAutoSave(text) {
    this.log("AutoSave: display");
    const el = document.getElementById(this.AUTO_SAVE_ELEMENT_ID);
    if (el) {
      el.innerHTML = text;
    }
  }

  autoSave() {
    this.log("AutoSave: save");
    let f;
    for (let i = 0; i < this.onAutoSave.length; i++) {
      f = this.onAutoSave[i];
      f();
    }
    this.resetAutoSave();
  }
}
