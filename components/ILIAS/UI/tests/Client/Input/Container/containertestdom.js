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
 */

import { JSDOM } from 'jsdom';

export default class ContainerTestDOM {
  /**
   * @var JSDOM
   */
  simple;

  switchableGroup;

  constructor() {
    this.simple = this.#getSimple();
    this.switchableGroup = this.#getSwitchableGroup();
  }

  #getSimple() {
    return new JSDOM(
      `
      <div class="c-input-markdown">
          <form role="form" id="test_container_id" class="il-standard-form form-horizontal" enctype="multipart/form-data" action="#" method="post" novalidate="novalidate">
            <div class="form-group row">
                <label for="txt_1" class="control-label">Item 1</label>
                <input id="txt_1" type="text" value="value_1" name="form/input_0" class="form-control">
            </div>
            <div class="form-group row">
                <label for="txt_2" class="control-label">Item 3</label>
                <input id="txt_2" type="text" value="value_2" name="form/input_1" class="form-control">
            </div>
            <div class="form-group row">
                <label for="txt_3" class="control-label">Item 3</label>
                <input id="txt_4" type="text" value="value_3" name="form/input_2" class="form-control">
            </div>
          </form>
      </div>
      `,
      {
        url: 'https://localhost',
      },
    );
  }

  #getSwitchableGroup() {
    return new JSDOM(
      `
      <div class="c-input-markdown">
          <form role="form" id="test_container_id" class="il-standard-form form-horizontal" enctype="multipart/form-data" action="#" method="post" novalidate="novalidate">
              <div class="form-group row">

                  <div class="form-control il-input-radiooption">
                      <input type="radio" id="option_1" name="form/input_0" value="1" checked="checked">
                      <label for="option_1">Switchable Group</label>
                      <div class="form-group row">
                          <label for="txt_1.1" class="control-label">Item 1.1</label>
                          <input id="txt_1.1" type="text" value="value_1.1" name="form/input_0/input_1/input_2" class="form-control">
                      </div>
                      <div class="form-group row">
                          <label for="txt_1.2" class="control-label">Item 1.2</label>
                          <div>
                              <input id="txt_1.2" type="text" value="value_1.2"name="form/input_0/input_1/input_3" class="form-control">
                          </div>
                      </div>
                  </div>

                  <div class="form-control il-input-radiooption">
                      <input type="radio" id="option_2" name="form/input_0" value="2">
                      <label for="option_1">Switchable Group</label>
                      <div class="form-group row">
                          <label for="txt_2.1" class="control-label">Item 1.1</label>
                          <input id="txt_2.1" type="text" value="value_2.1" name="form/input_0/input_4/input_5" class="form-control">
                      </div>
                  </div>
              </div>
          </form>
      </div>
      `,
      {
        url: 'https://localhost',
      },
    );
  }
}
