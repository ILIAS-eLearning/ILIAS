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
 */

import {
  beforeEach, describe, expect, it,
} from '@jest/globals';
// import { JSDOM } from 'jsdom';

let last_timeout;
let last_timeout_time;

beforeEach((done) => {
  // last_timeout = () => {
  // };
  // last_timeout_time = 0;
  // JSDOM.fromFile('./tests/UI/Client/Toast/ToastTest.html',
  //   { runScripts: "dangerously", resources: "usable" })
  //   .then(dom => {
  //     global.window = dom.window;
  //     window.setTimeout = (callback, time) => {
  //       last_timeout = callback;
  //       last_timeout_time = time;
  //     };
  //     window.clearTimeout = element => {
  //       last_timeout = () => {
  //       };
  //       last_timeout_time = 0;
  //     };
  //     window.XMLHttpRequest = class {
  //       open(mode, url) {
  //         global.last_xhr_url = url;
  //       };
  //
  //       send() {
  //       };
  //     }
  //     global.document = window.document;
  //     global.document.addEventListener('DOMContentLoaded', () => {
  //       global.element = document.querySelector('.il-toast-wrapper');
  //       global.toast = element.querySelector('.il-toast');
  //       global.il = document.il;
  //       done();
  //     });
  //   });
});

describe.skip('component available', () => {
  it('toast', () => {
    expect(il.UI.toast).not.toHaveLength(0);
  });
});

describe.skip('showToast', () => {
  it('before timeout', () => {
    il.UI.toast.showToast(element);
    expect(last_timeout_time).toBe(parseInt(element.dataset.delay));
    expect(toast.classList.contains('active')).toBe(false);
  })
  it('after timeout', () => {
    il.UI.toast.showToast(element);
    last_timeout();
    expect(toast.classList.contains('active')).toBe(true);
  })
})

describe.skip('setToastSettings', () => {
  it('set delay time', () => {
    element.dataset.delay = 123;
    il.UI.toast.setToastSettings(element);
    il.UI.toast.showToast(element);
    expect(last_timeout_time).toBe(123);
  })
  it('set vanish time', () => {
    element.dataset.vanish = 1111;
    il.UI.toast.setToastSettings(element);
    il.UI.toast.appearToast(element);
    expect(last_timeout_time).toBe(1111);
  })
})

describe.skip('appearToast', () => {
  it('show and arrange', () => {
    il.UI.toast.appearToast(element);
    expect(toast.classList.contains('active')).toBe(true);
  })
  it('trigger close action', () => {
    il.UI.toast.appearToast(element);
    toast.querySelector('.close').dispatchEvent(new window.Event('click'));
    expect(toast.classList.contains('active')).toBe(false);
  })
  it('trigger default vanish action', () => {
    il.UI.toast.appearToast(element);
    last_timeout();
    expect(toast.classList.contains('active')).toBe(false);
  })
})

describe.skip('closeToast', () => {
  it('initiate transition', () => {
    toast.classList.add('active')
    il.UI.toast.closeToast(element);
    expect(toast.classList.contains('active')).toBe(false);
  })
  it('remove wrapper', () => {
    il.UI.toast.closeToast(element);
    toast.dispatchEvent(new window.Event('transitionend'));
    expect(element.parentNode).toBeNull();
  })
  it('send close request', () => {
    il.UI.toast.closeToast(element, true);
    toast.dispatchEvent(new window.Event('transitionend'));
    expect(last_xhr_url).toContain(element.dataset.vanishurl);
  })
})

describe.skip('stopToast', () => {
  it('prevent default vanish action', () => {
    il.UI.toast.appearToast(element);
    toast.dispatchEvent(new window.Event('mouseenter'));
    last_timeout();
    expect(toast.classList.contains('active')).toBe(true);
  })
  it('reestablish vanish action', () => {
    il.UI.toast.appearToast(element);
    toast.dispatchEvent(new window.Event('mouseenter'));
    last_timeout();
    toast.dispatchEvent(new window.Event('mouseleave'));
    last_timeout();
    expect(toast.classList.contains('active')).toBe(false);
  })
  it('enforce close on prevention', () => {
    il.UI.toast.appearToast(element);
    toast.dispatchEvent(new window.Event('mouseenter'));
    toast.querySelector('.close').dispatchEvent(new window.Event('click'));
    expect(toast.classList.contains('active')).toBe(false);
  })
})
