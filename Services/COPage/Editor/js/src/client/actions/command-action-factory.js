/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

import COPageCommandActionFactory from './copage-command-action-factory.js';

/**
 * Command action factory
 */
export default class CommandActionFactory {

  /**
   */
  constructor() {
  }

  /**
   * @returns {COPageCommandActionFactory}
   */
  copage() {
    return new COPageCommandActionFactory();
  }
}