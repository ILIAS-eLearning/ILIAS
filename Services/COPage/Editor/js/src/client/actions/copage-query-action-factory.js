/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

import QueryAction from "./query-action.js";

/**
 * COPage action factory
 *
 */
export default class COPageQueryActionFactory {

  COMPONENT = "copage";
  UI_ALL = "ui.all";

  /**
   */
  constructor() {
  }

  uiAll() {
    return new QueryAction(this.COMPONENT, this.UI_ALL);
  }
}