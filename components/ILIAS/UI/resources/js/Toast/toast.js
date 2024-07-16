var il = il || {};
il.UI = il.UI || {};
il.UI.toast = ((UI) => {
    let vanishTime = 5000;
    let delayTime = 500;
    let queue = new WeakMap();

    const setToastSettings = (element) => {
        if (element.hasAttribute('data-vanish')) {
            vanishTime = parseInt(element.dataset.vanish);
        }
        if (element.hasAttribute('data-delay')) {
            delayTime = parseInt(element.dataset.delay);
        }
    }

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
        setToastSettings: setToastSettings,
    }
})(il.UI)
