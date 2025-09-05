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

/** @type {string} */
const UNDEFINED_DATE = 'TBD';

/**
 * @param {Field} field
 * @param {FieldDisplayValue|null} start
 * @param {FieldDisplayValue|null} end
 * @returns {FieldDisplayValue|null}
 */
export default function reduceDurationField(field, [start, end]) {
  if (start === null && end === null) {
    return new FieldDisplayValue(
      field,
      `${start?.value ?? UNDEFINED_DATE} - ${end?.value ?? UNDEFINED_DATE}`,
    );
  }
  return null;
}
