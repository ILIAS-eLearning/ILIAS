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
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */

import * as FieldType from '../FieldType.js';
import reduceLinkField from './reduceLinkField.js';
import reduceGroupField from './reduceGroupField.js';
import reduceMultiSelectField from './reduceMultiSelectField.js';
import reduceOptionalGroupField from './reduceOptionalGroupField.js';
import reduceSwitchableGroupField from './reduceSwitchableGroupField.js';
import reduceValueAttributeOfField from './reduceValueAttributeOfField.js';
import reduceCheckedAttributeOfField from './reduceCheckedAttributeOfField.js';
import addFilterContainerFieldReducers from './addFilterContainerFieldReducers.js';

/**
 * Adds all reducers for Form Container Input's to the given instance.
 *
 * @param {Container} form
 */
export default function addFormContainerFieldReducers(form) {
  form.addFieldReducer(FieldType.GROUP, reduceGroupField);
  form.addFieldReducer(FieldType.OPTIONAL_GROUP, reduceOptionalGroupField);
  form.addFieldReducer(FieldType.SWITCHABLE_GROUP, reduceSwitchableGroupField);
  form.addFieldReducer(FieldType.SECTION, reduceGroupField);
  form.addFieldReducer(FieldType.CHECKBOX, reduceCheckedAttributeOfField);
  form.addFieldReducer(FieldType.TEXTAREA, reduceValueAttributeOfField);
  form.addFieldReducer(FieldType.RADIO, reduceCheckedAttributeOfField);
  form.addFieldReducer(FieldType.MULTI_SELECT, reduceMultiSelectField);
  form.addFieldReducer(FieldType.URL, reduceValueAttributeOfField);
  form.addFieldReducer(FieldType.LINK, reduceLinkField);
  form.addFieldReducer(FieldType.COLOR_SELECT, reduceValueAttributeOfField);
  form.addFieldReducer(FieldType.MARKDOWN, reduceValueAttributeOfField);
  // for now, all Filter Container Input's are Form Container Input's too
  addFilterContainerFieldReducers(form);
}
