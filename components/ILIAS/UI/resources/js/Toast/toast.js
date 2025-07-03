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

var il = il || {};
il.UI = il.UI || {};
il.UI.toast = ((UI) => {
    let vanishTime = 5000;
    let delayTime = 500;
    let queue = new WeakMap();

    const showToast = (element) => {
        setTimeout(() => {appearToast(element);}, delayTime);
    }

    const closeToast = (element, forced = false) => {
        element.querySelector('.il-toast').addEventListener('transitionend', () => {
            if (forced && element.dataset.vanishurl !== '') {
                let xhr = new XMLHttpRequest();
                xhr.open('GET', element.dataset.vanishurl);
                xhr.send();
            }
            element.remove();
            element.dispatchEvent(new Event('removeToast'));
        })
        element.querySelector('.il-toast').classList.remove('active');
    };

    const appearToast = (element) => {
        element.querySelector('.il-toast').classList.add('active');
        queueToast(element);
        element.querySelector('.il-toast .close').addEventListener('click', () => closeToast(element, true));
        element.querySelector('.il-toast').addEventListener('mouseenter', () => stopToast(element));
        element.querySelector('.il-toast').addEventListener('focusin', () => stopToast(element));
        element.querySelector('.il-toast').addEventListener('mouseleave', () => queueToast(element));
        element.querySelector('.il-toast').addEventListener('focusout', () => queueToast(element));
    }

    const stopToast = (element) => {
        clearTimeout(queue.get(element));
    }

    const queueToast = (element) => {
        queue.set(element, setTimeout(() => closeToast(element), vanishTime));
    }

    return {
        showToast: showToast,
        closeToast: closeToast,
        appearToast: appearToast,
    }
})(il.UI)
