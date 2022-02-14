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
        })
        element.querySelector('.il-toast').classList.remove('active');
    };

    let getRelativeAnchestor = (x) => {
        let pos = window.getComputedStyle(x).position;
        if (pos ==='relative' || pos === 'fixed' || pos === 'sticky' || !x.parentNode) {
            return x;
        } else {
            return getRelativeAnchestor(x.parentNode);
        }
    };

    let appearToast = (element) => {
        let item = getRelativeAnchestor(element);
        let height = 0;
        let item_top = item.getBoundingClientRect().top;
        item.querySelectorAll('.il-toast').forEach((e) => {
            let temp_height = e.getBoundingClientRect().bottom - item_top;
            if(e !== element.querySelector('.il-toast') && e.classList.contains('active') && height < temp_height) {
                height = temp_height;
            }
        })

        element.style.top = height + 'px';
        element.querySelector('.il-toast').classList.add('active');
        element.querySelector('.il-toast .close').addEventListener('click', () => {closeToast(element, true);});
        setTimeout(() => {closeToast(element);}, vanishTime);
    }

    return {
        showToast: showToast,
        closeToast: closeToast,
        appearToast: appearToast,
        setToastSettings: setToastSettings,
        getRelativeAnchestor: getRelativeAnchestor
    }
})(il.UI)
il.UI.toast.setToastSettings(document.currentScript.parentElement);
il.UI.toast.showToast(document.currentScript.parentElement);
