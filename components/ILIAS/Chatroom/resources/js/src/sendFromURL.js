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

export default url => (action, getParameters = {}) => {
  const target = new URL(url.replace(/postMessage/, action));
  Object.entries(getParameters).forEach(kv => set(target.searchParams, ...kv));

  return fetch(target);
}

function set(s, k, v) {
  if (typeof v === 'object' && v !== null) {
    Object.entries(v).forEach(([nk, nv]) => set(s, k + '[' + nk + ']', nv));
  } else {
    s.set(k, v);
  }
};
