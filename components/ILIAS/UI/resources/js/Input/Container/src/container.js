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

import { SwitchableGroupHook } from './fieldhooks/switchablegroup.hook.js';
import { OptionalGroupHook } from './fieldhooks/optionalgroup.hook.js';
import { RadioHook } from './fieldhooks/radio.hook.js';
import { PasswordHook } from './fieldhooks/password.hook.js';
import { DurationHook } from './fieldhooks/duration.hook.js';
import { LinkHook } from './fieldhooks/link.hook.js';
import { SelectHook } from './fieldhooks/select.hook.js';
import { MultiSelectHook } from './fieldhooks/multiselect.hook.js';

import filter from './filter.main.js';
import ContainerFactory from './container.factory.js';

const hooks = {
  'switchable-group-field-input': SwitchableGroupHook,
  'optional-group-field-input': OptionalGroupHook,
  'radio-field-input': RadioHook,
  'multiSelect-field-input': MultiSelectHook,
  'password-field-input': PasswordHook,
  'duration-field-input': DurationHook,
  'link-field-input': LinkHook,
  'select-field-input': SelectHook,
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
il.UI.Input.Container = new ContainerFactory(hooks);
