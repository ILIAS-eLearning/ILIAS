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

module.exports.each = (array, proc) => array.reduce(
  (p, n) => p.then(() => toPromise(proc)(n)),
  Promise.resolve()
);

module.exports.toPromise = toPromise;
module.exports.fromPromise = fromPromise;

function toPromise(proc)
{
  return (...args) => new Promise(
    (resolve, reject) => proc(...args, (err, val) => err ? reject(err) : resolve(val))
  );
}

function fromPromise(promise, proc)
{
  promise.then(val => proc(null, val));
  promise.catch(err => proc(err));
}
