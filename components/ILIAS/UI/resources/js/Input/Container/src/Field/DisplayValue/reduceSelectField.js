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

import FieldDisplayValue from './FieldDisplayValue.js';

/**
 * @param {Field} field
 * @returns {FieldDisplayValue|null}
 */
export default function reduceSelectField(field) {
  const value = field.getInputs()[0]
    ?.selectedOptions[0]
    ?.textContent
    ?.replace(/\s+/g, '');
  if (value && value !== '-') {
    return new FieldDisplayValue(field, value);
  }
  return null;
}
