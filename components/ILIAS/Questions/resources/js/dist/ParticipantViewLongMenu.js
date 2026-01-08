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

(function () {
  const longmenu = () => {
    const init = (input, autocompleteLength, answerOptions) => {
      if (input.nodeName === 'INPUT') {
        let longest = answerOptions.reduce((a, b) => {
          return a.length > b.length ? a : b;
        });
        input.setAttribute('size', longest.length);
        input.addEventListener(
          'keyup',
          (e) => { keyHandler(autocompleteLength, answerOptions, e); }
        );
      };
    };

    const keyHandler = (autocompleteLength, answerOptions, e) => {
      if (e.key === 'Enter' && e.target.nodeName === 'LI') {
        e.stopImmediatePropagation();
        e.preventDefault();
        onSelectHandler(e);
        return;
      }

      if (e.key === 'ArrowDown') {
        e.stopImmediatePropagation();
        e.preventDefault();
        if (e.target.nextElementSibling?.nodeName === 'UL') {
          e.target.nextElementSibling.firstElementChild.focus();
        }

        if (e.target.nodeName === 'LI' && e.target.nextElementSibling !== null) {
          e.target.nextElementSibling.focus();
        }
        return;
      }

      if (e.key === 'ArrowUp' && e.target.nodeName === 'LI') {
        e.stopImmediatePropagation();
        e.preventDefault();
        if (e.target.previousElementSibling === null) {
          e.target.parentElement.previousElementSibling.focus();
        } else {
          e.target.previousElementSibling.focus();
        }
        return;
      }

      onChangeHandler(autocompleteLength, answerOptions, e);
    };

    const onChangeHandler = (autocompleteLength, answerOptions, e) => {
      if (e.target.nextElementSibling?.nodeName === 'UL') {
        e.target.nextElementSibling.remove();
      }

      if (e.key === 'Tab' || e.target.value.length < autocompleteLength) {
        return;
      }

      const matchingAnswers = answerOptions.filter((answer) => {
        return answer.toLowerCase().includes(e.target.value.toLowerCase())
      });

      if (matchingAnswers.length === 0) {
        return;
      }

      let list = document.createElement('ul');
      matchingAnswers.forEach((answer) => {
        let listElement = document.createElement('li');
        listElement.tabIndex = 0;
        listElement.textContent = answer;
        list.appendChild(listElement);
      });
      list.addEventListener('click', onSelectHandler);
      list.addEventListener('keyup', onSelectHandler);
      e.target.parentNode.appendChild(list);
    };

    const onSelectHandler = (e) => {
      if (e.type === 'keydown' && e.key !== 'Enter') {
        return;
      }
      e.target.parentNode.previousElementSibling.value = e.target.textContent;
      e.target.parentNode.previousElementSibling.focus();
      e.target.parentNode.remove();
    };

    const public_interface = {
      init
    };
    return public_interface;
  };

  il = il || {};
  il.test = il.test || {};
  il.test.player = il.test.player || {};
  il.test.player.longmenu = longmenu();
}());
