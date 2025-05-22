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
 *********************************************************************/

const formatToXDigits = digits => nr => '0'.repeat(Math.max(0, digits - String(nr).length)) + nr;

export default (format, dateOrTime) => {
  const date = dateOrTime instanceof Date ? dateOrTime : new Date(dateOrTime);
  const twoD = formatToXDigits(2);

  return [
    ['Y', date.getFullYear()],
    ['m', twoD(date.getMonth() + 1)],
    ['d', twoD(date.getDate())],
    ['h', twoD((date.getHours() % 12) || 12)],
    ['H', twoD(date.getHours())],
    ['i', twoD(date.getMinutes())],
    ['s', twoD(date.getSeconds())],
    ['a', date.getHours() > 11 ? 'pm' : 'am'],
  ].reduce((format, [from, to]) => format.replace(from, to), format);
};
