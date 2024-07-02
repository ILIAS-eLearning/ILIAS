var il = il || {};
il.UI = il.UI || {};
il.UI.toast = ((UI) => {
    let container = document.querySelector('.il-toast-container');
    let vanishTime = container.dataset.vanish;
    let delayTime = container.dataset.delay;

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
    }
})(il.UI)
