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

/**
 * This is a special reducer because it only returns the results of its children,
 * if there are any. We extract the child-results to keep a Group Field virtual,
 * since it is not rendered as an actual input.
 *
 * @param {Field} field
 * @param {FieldDisplayValue[]} childResults
 * @returns {FieldDisplayValue[]|null}
 */
export default function reduceGroupField(field, childResults) {
  const results = childResults.filter((result) => result !== null);
  if (results.length > 0) {
    return results;
  }
  return null;
}
