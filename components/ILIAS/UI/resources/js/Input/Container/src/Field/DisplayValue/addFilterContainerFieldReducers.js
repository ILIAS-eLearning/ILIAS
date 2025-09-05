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
import reduceValueAttributeOfField from './reduceValueAttributeOfField.js';
import reduceSelectField from './reduceSelectField.js';
import reduceDurationField from './reduceDurationField.js';

/**
 * Adds all reducers for Filter Container Input's to the given instance.
 *
 * @param {Container} filter
 */
export default function addFilterContainerFieldReducers(filter) {
  filter.addFieldReducer(FieldType.TEXT, reduceValueAttributeOfField);
  filter.addFieldReducer(FieldType.NUMERIC, reduceValueAttributeOfField);
  filter.addFieldReducer(FieldType.SELECT, reduceSelectField);
  filter.addFieldReducer(FieldType.DATETIME, reduceValueAttributeOfField);
  filter.addFieldReducer(FieldType.DURATION, reduceDurationField);
}
