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

il.Dashboard.showModalOnSort = function(node, url, selectSignal, signals){
  node.querySelectorAll('span[data-action]').forEach(n => {
    n.parentNode.setAttribute('data-action', n.getAttribute('data-action'));
    const parent = n.parentNode;
    const text = n.textContent;
    n.remove();
    parent.textContent += text;
  });

  $(document).on(selectSignal, (e, data) => {
    if(signals[data.options.sortation]){
      $(document).trigger(signals[data.options.sortation], {});
    } else {
      window.location = url + '&sorting=' + data.options.sortation;
    }
  });
};

il.Dashboard.moveModalButtons = function(node){
  const cancel = node.querySelectorAll('form')[3].querySelector('button');
  const save = node.querySelector('button.btn-default');
  node.querySelectorAll('form')[3].insertBefore(save, cancel);
  save.addEventListener('click', e => {
    e.preventDefault();
    node.querySelectorAll('form')[2].submit();
  });
};
