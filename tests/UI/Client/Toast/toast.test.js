import {expect} from "chai";
import {JSDOM} from 'jsdom';

global.setTimeout = (callback, time) => {
    global.last_timeout = callback;
    global.last_timeout_time = time;
}

beforeEach( (done) => {
    JSDOM.fromFile('./tests/UI/Client/Toast/ToastTest.html', { runScripts: "dangerously", resources: "usable"})
        .then(dom => {
            global.window = dom.window;
            window.XMLHttpRequest = class {
                open(mode, url) {global.last_xhr_url = url;};
                send(){};
            }
            global.document = window.document;
            global.document.addEventListener('DOMContentLoaded', () => {
                global.element = document.querySelector('.il-toast-wrapper');
                global.toast = element.querySelector('.il-toast');
                global.il = document.il;
                done();
            });
        });
});

describe('component available', () => {
    it('toast',  () => {
        expect(il.UI.toast).to.not.be.empty;
    });
});

describe('showToast', () => {
    it ('before timeout', () => {
        il.UI.toast.showToast(element);
        expect(last_timeout_time).to.be.equal(parseInt(element.dataset.delay));
        expect(toast.classList.contains('active')).to.be.false;
    })
    it ('after timeout', () => {
        il.UI.toast.showToast(element);
        last_timeout();
        expect(toast.classList.contains('active')).to.be.true;
    })
})

describe('setToastSettings', () => {
    it ('set delay time', () => {
        element.dataset.delay = 123;
        il.UI.toast.setToastSettings(element);
        il.UI.toast.showToast(element);
        expect(last_timeout_time).to.be.equal(123);
    })
    it ('set vanish time', () => {
        element.dataset.vanish = 1111;
        il.UI.toast.setToastSettings(element);
        il.UI.toast.appearToast(element);
        expect(last_timeout_time).to.be.equal(1111);
    })
})

describe('appearToast', () => {
    it ('show and arrange', () => {
        il.UI.toast.appearToast(element);
        expect(toast.classList.contains('active')).to.be.true;
    })
    it ('trigger close action', () => {
        il.UI.toast.appearToast(element);
        toast.querySelector('.close').dispatchEvent(new window.Event('click'));
        expect(toast.classList.contains('active')).to.be.false;
    })
    it ('trigger default vanish action', () => {
        il.UI.toast.appearToast(element);
        last_timeout();
        expect(toast.classList.contains('active')).to.be.false;
    })
})

describe('closeToast', () => {
    it ('initiate transition', () => {
        toast.classList.add('active')
        il.UI.toast.closeToast(element);
        expect(toast.classList.contains('active')).to.be.false;
    })
    it ('remove wrapper', () => {
        il.UI.toast.closeToast(element);
        toast.dispatchEvent(new window.Event('transitionend'));
        expect(element.parentNode).to.be.null;
    })
    it ('send close request', () => {
        il.UI.toast.closeToast(element, true);
        toast.dispatchEvent(new window.Event('transitionend'));
        expect(last_xhr_url).to.be.string(element.dataset.vanishurl);
    })
})
