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

/** @type {string} delimiter for concatenated options */
const DELIMITER = ', ';

/**
 * @param {Field} field
 * @return {FieldDisplayValue|null}
 */
export default function reduceMultiSelectField(field) {
  const checkedOptions = field.getInputs().filter((checkbox) => checkbox.checked);
  if (!checkedOptions) {
    return new FieldDisplayValue(
      field,
      checkedOptions
        .map((checkbox) => checkbox.parentElement.textContent)
        .join(DELIMITER),
    );
  }
  return null;
}
