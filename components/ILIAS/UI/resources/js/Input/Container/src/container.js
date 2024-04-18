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

import SwitchableGroupTransforms from './transforms/switchablegroup.transform';
import OptionalGroupTransforms from './transforms/optionalgroup.transform';
import RadioTransforms from './transforms/radio.transform';
import PasswordTransforms from './transforms/password.transform';
import DurationTransforms from './transforms/duration.transform';
import LinkTransforms from './transforms/link.transform';
import SelectTransforms from './transforms/select.transform';

import filter from './filter.main';
import ContainerFactory from './container.factory';

const transforms = {};
transforms.SwitchableGroupFieldInput = new SwitchableGroupTransforms();
transforms.OptionalGroupFieldInput = new OptionalGroupTransforms();
transforms.RadioFieldInput = new RadioTransforms();
transforms.MultiSelectFieldInput = new RadioTransforms();
transforms.PasswordFieldInput = new PasswordTransforms();
transforms.DurationFieldInput = new DurationTransforms();
transforms.LinkFieldInput = new LinkTransforms();
transforms.SelectFieldInput = new SelectTransforms();

il = il || {};
il.UI = il.UI || {};
il.UI.filter = filter($);
il.UI.Input = il.UI.Input || {};
il.UI.Input.Container = new ContainerFactory(transforms);
