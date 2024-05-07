var il = il || {};
il.UI = il.UI || {};
il.UI.toast = ((UI) => {
    let vanishTime = 5000;
    let delayTime = 500;

    let setToastSettings = (element) => {
        if (element.hasAttribute('data-vanish')) {
            vanishTime = parseInt(element.dataset.vanish);
        }
        if (element.hasAttribute('data-delay')) {
            delayTime = parseInt(element.dataset.delay);
        }
    }

    let showToast = (element) => {
        setTimeout(() => {appearToast(element);}, delayTime);
    }

    let closeToast = (element, forced = false) => {
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

    let appearToast = (element) => {
        element.querySelector('.il-toast').classList.add('active');
        element.querySelector('.il-toast .close').addEventListener('click', () => {closeToast(element, true);});
        setTimeout(() => {closeToast(element);}, vanishTime);
    }

    return {
        showToast: showToast,
        closeToast: closeToast,
        appearToast: appearToast,
        setToastSettings: setToastSettings,
    }
})(il.UI)