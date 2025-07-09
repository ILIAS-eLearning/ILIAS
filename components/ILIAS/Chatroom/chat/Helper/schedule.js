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

const s2ms = s => s * 1000;
const m2ms = m => m * 60000;
const h2ms = h => h * 3600000;

const startRepeat = (startIn, repeatEvery, thunk) => setTimeout(() => {
  thunk();
  setInterval(thunk, repeatEvery);
}, startIn);

module.exports.everyDayAt = (hour, minute, thunk) => {
  const now = new Date();
  const ms = (h2ms(24) +
              (h2ms(hour) + m2ms(minute)) -
              (h2ms(now.getHours()) + m2ms(now.getMinutes()) + s2ms(now.getSeconds()))) %
        h2ms(24);

  startRepeat(ms, h2ms(24), thunk);
};

module.exports.every20Minutes = thunk => {
  const now = new Date();
  const rest = (m2ms(now.getMinutes()) + s2ms(now.getSeconds())) % m2ms(20);
  const ms = m2ms(20) - rest;
  startRepeat(ms, m2ms(20), thunk);
};
