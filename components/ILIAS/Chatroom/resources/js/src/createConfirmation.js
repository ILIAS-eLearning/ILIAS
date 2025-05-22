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

import bus from './bus';

const Void = () => {};

const load = key => new Promise(resolve => bus.onArrived(key, ({node, showModal, closeModal}) => {
  let next = Void;
  node.querySelector('form').addEventListener('submit', e => {
    if (e.submitter && e.submitter.getAttribute('data-dismiss') === 'modal') {
      return;
    }
    e.preventDefault();
    next(true);
    next = Void;
    closeModal();
    return false;
  });
  resolve(() => {
    showModal();
    return new Promise(ok => {
      next = ok;
    });
  });
}));

const cached = proc => {
  const cache = {};
  return key => {
    if (!cache[key]) {
      cache[key] = proc(key);
    }

    return cache[key];
  };
};

/**
 * @returns {function(string): Promise.<boolean>}
 */
export default () => {
  const cachedLoad = cached(load);
  return key => cachedLoad(key).then(f => f());
};
