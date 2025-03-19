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

import {expect} from "chai";
import {JSDOM} from 'jsdom';

let last_timeout;
let last_timeout_time;

beforeEach( (done) => {
    last_timeout = () => {};
    last_timeout_time = 0;
    JSDOM.fromFile('./tests/UI/Client/Toast/ToastTest.html', { runScripts: "dangerously", resources: "usable"})
        .then(dom => {
            global.window = dom.window;
            window.setTimeout = (callback, time) => {
                last_timeout = callback;
                last_timeout_time = time;
            };
            window.clearTimeout = element => {
                last_timeout = () => {};
                last_timeout_time = 0;
            };
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
        expect(toast.classList.contains('active')).to.be.false;
    })
    it ('after timeout', () => {
        il.UI.toast.showToast(element);
        last_timeout();
        expect(toast.classList.contains('active')).to.be.true;
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

describe('stopToast', () => {
    it ('prevent default vanish action', () => {
        il.UI.toast.appearToast(element);
        toast.dispatchEvent(new window.Event('mouseenter'));
        last_timeout();
        expect(toast.classList.contains('active')).to.be.true;
    })
    it ('reestablish vanish action', () => {
        il.UI.toast.appearToast(element);
        toast.dispatchEvent(new window.Event('mouseenter'));
        last_timeout();
        toast.dispatchEvent(new window.Event('mouseleave'));
        last_timeout();
        expect(toast.classList.contains('active')).to.be.false;
    })
    it ('enforce close on prevention', () => {
        il.UI.toast.appearToast(element);
        toast.dispatchEvent(new window.Event('mouseenter'));
        toast.querySelector('.close').dispatchEvent(new window.Event('click'));
        expect(toast.classList.contains('active')).to.be.false;
    })
})
