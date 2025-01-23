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

import $ from 'jquery';

import SwitchableGroupTransforms from './transforms/switchablegroup.transform.js';
import OptionalGroupTransforms from './transforms/optionalgroup.transform.js';
import RadioTransforms from './transforms/radio.transform.js';
import PasswordTransforms from './transforms/password.transform.js';
import DurationTransforms from './transforms/duration.transform.js';
import LinkTransforms from './transforms/link.transform.js';
import SelectTransforms from './transforms/select.transform.js';
import MultiSelectTransforms from './transforms/multiselect.transform.js';

import filter from './filter.main.js';
import ContainerFactory from './container.factory.js';

const transforms = {
  'switchable-group-field-input': new SwitchableGroupTransforms(),
  'optional-group-field-input': new OptionalGroupTransforms(),
  'radio-field-input': new RadioTransforms(),
  'multiSelect-field-input': new MultiSelectTransforms(),
  'password-field-input': new PasswordTransforms(),
  'duration-field-input': new DurationTransforms(),
  'link-field-input': new LinkTransforms(),
  'select-field-input': new SelectTransforms(),
};

il = il || {};
il.UI = il.UI || {};
il.UI.filter = filter($);
il.UI.Input = il.UI.Input || {};

/**
 * This provides client side access to form nodes and their values.
 *
 * Retrieve a form
 *  const form = il.UI.Input.Container.get(form.id);
 * and get its nodes
 *  const formparts = form.getNodes();
 * or a specific node, e.g.
 *  const node = form.getNodeByName('form/input_4/input_6');
 * With a node, you may retrieve its value representation:
 *  values = form.getValuesRepresentation(node);
 * You may also get all nodes in a flat array, either starting at
 * the container or by specifying a specific start-node:
 *  const allNodes = form.getNodesFlat(node);
 */
il.UI.Input.Container = new ContainerFactory(transforms);
