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

il.Dashboard = il.Dashboard || {};

il.Dashboard.confirmModal = function (node) {
  node.querySelectorAll('.alert').forEach((e) => {
    e.hidden = true;
  });
  node.querySelector('button[id]').addEventListener('click', (e) => {
    if (node.querySelector('.alert-warning').hidden) {
      e.preventDefault();
      e.stopPropagation();
    }
    if (node.querySelector('input:checked') !== null) {
      node.querySelector('.alert-info').hidden = true;
      node.querySelector('.alert-warning').hidden = false;
      node.querySelectorAll('input').forEach((input) => {
        input.hidden = true;
        input.closest('li').hidden = true;
        input.closest('ul').parentNode.parentNode.style.display = 'none';
      });
      node.querySelectorAll('input:checked').forEach((input) => {
        input.closest('li').hidden = false;
        input.closest('ul').parentNode.parentNode.style.display = '';
      });
    } else {
      node.querySelector('.alert-info').hidden = false;
    }
  }, true);
};
