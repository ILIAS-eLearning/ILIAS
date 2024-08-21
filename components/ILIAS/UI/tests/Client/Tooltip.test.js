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

import { describe, expect, it } from '@jest/globals';
import Tooltip from '../../resources/js/Core/src/core.Tooltip';

describe('tooltip class exists', () => {
  it('Tooltip', () => {
    expect(Tooltip).toBeDefined();
  });
});

describe('tooltip initializes', () => {
  const addEventListenerElement = [];
  const addEventListenerContainer = [];
  const getAttribute = [];
  const getElementById = [];

  const window = {};

  const document = {
    getElementById(which) {
      getElementById.push(which);
      return tooltip;
    },
    getElementsByTagName(tag) {
      return [];
    },
    parentWindow: window,
  };

  const container = {
    addEventListener(ev, handler) {
      addEventListenerContainer.push({ e: ev, h: handler });
    },
  };

  const element = {
    addEventListener(ev, handler) {
      addEventListenerElement.push({ e: ev, h: handler });
    },
    getAttribute(which) {
      getAttribute.push(which);
      return `attribute-${which}`;
    },
    ownerDocument: document,
    parentElement: container,
  };

  var tooltip = {
    style: {
      transform: null,
    },
  };

  const object = new Tooltip(element);
  object.checkVerticalBounds = function () {};
  object.checkHorizontalBounds = function () {};

  it('searches tooltip element', () => {
    expect(getAttribute).toContain('aria-describedby');
    expect(getElementById).toContain('attribute-aria-describedby');
  });

  it('binds events on element', () => {
    expect(addEventListenerElement).toContainEqual(expect.objectContaining({ e: 'focus', h: object.showTooltip }));
    expect(addEventListenerElement).toContainEqual(expect.objectContaining({ e: 'blur', h: object.hideTooltip }));
  });

  it('binds events on container', () => {
    expect(addEventListenerContainer).toContainEqual(expect.objectContaining({ e: 'mouseenter', h: object.showTooltip }));
    expect(addEventListenerContainer).toContainEqual(expect.objectContaining({ e: 'touchstart', h: object.showTooltip }));
    expect(addEventListenerContainer).toContainEqual(expect.objectContaining({ e: 'mouseleave', h: object.hideTooltip }));
  });
});

describe('tooltip show works', () => {
  const addEventListenerDocument = [];
  let classListAdd = [];

  const window = {};

  const document = {
    addEventListener(ev, handler) {
      addEventListenerDocument.push({ e: ev, h: handler });
    },
    getElementById(which) {
      return tooltip;
    },
    getElementsByTagName(tag) {
      return [];
    },
    parentWindow: window,
  };

  const container = {
    addEventListener(ev, handler) {
    },
    classList: {
      add(which) {
        classListAdd.push(which);
      },
      remove(which) {
        classListRemove.push(which);
      },
    },
  };

  const element = {
    addEventListener(ev, handler) {
    },
    getAttribute(which) {
      return `attribute-${which}`;
    },
    ownerDocument: document,
    parentElement: container,
  };

  var tooltip = {
    style: {
      transform: null,
    },
  };

  const object = new Tooltip(element);
  object.checkVerticalBounds = function () {};
  object.checkHorizontalBounds = function () {};

  it('binds events on document', () => {
    classListAdd = [];

    expect(addEventListenerDocument).toContainEqual(expect.not.objectContaining({ e: 'keydown', h: object.onKeyDown }));
    expect(addEventListenerDocument).toContainEqual(expect.not.objectContaining({ e: 'pointerdown', h: object.onPointerDown }));

    object.showTooltip();

    expect(addEventListenerDocument).toContainEqual(expect.objectContaining({ e: 'keydown', h: object.onKeyDown }));
    expect(addEventListenerDocument).toContainEqual(expect.objectContaining({ e: 'pointerdown', h: object.onPointerDown }));
  });

  it('adds visibility classes from tooltip', () => {
    classListAdd = [];

    object.showTooltip();

    expect(classListAdd).toEqual(['c-tooltip--visible']);
  });
});

describe('tooltip hide works', () => {
  let classListRemove = [];
  const removeEventListener = [];

  const window = {};

  const document = {
    addEventListener(ev, handler) {
    },
    removeEventListener(ev, handler) {
      removeEventListener.push({ e: ev, h: handler });
    },
    getElementById(which) {
      return tooltip;
    },
    getElementsByTagName(tag) {
      return [];
    },
    parentWindow: window,
  };

  const container = {
    addEventListener(ev, handler) {
    },
    classList: {
      add(which) {
      },
      remove(which) {
        classListRemove.push(which);
      },
    },
  };

  const element = {
    addEventListener(ev, handler) {
    },
    getAttribute(which) {
      return `attribute-${which}`;
    },
    ownerDocument: document,
    parentElement: container,
  };

  var tooltip = {
    style: {
      transform: null,
    },
  };

  const object = new Tooltip(element);
  object.checkVerticalBounds = function () {};
  object.checkHorizontalBounds = function () {};

  it('unbinds events on document when tooltip hides', () => {
    expect(removeEventListener).not.toContain(expect.objectContaining({ e: 'keydown', h: object.onKeyDown }));
    expect(removeEventListener).not.toContain(expect.objectContaining({ e: 'pointerdown', h: object.onPointerDown }));

    object.hideTooltip();

    expect(removeEventListener).toContainEqual(expect.objectContaining({ e: 'keydown', h: object.onKeyDown }));
    expect(removeEventListener).toContainEqual(expect.objectContaining({ e: 'pointerdown', h: object.onPointerDown }));
  });

  it('removes visibility classes from tooltip', () => {
    classListRemove = [];

    object.hideTooltip();

    expect(classListRemove).toEqual(['c-tooltip--visible', 'c-tooltip--top']);
  });

  it('hides on escape key', () => {
    let hideTooltipCalled = false;
    const keep = object.hideTooltip;
    object.hideTooltip = function () {
      hideTooltipCalled = true;
    };

    object.onKeyDown({ key: 'Escape' });

    expect(hideTooltipCalled).toBe(true);
    object.hideTooltip = keep;
  });

  it('hides on esc key', () => {
    let hideTooltipCalled = false;
    const keep = object.hideTooltip;
    object.hideTooltip = function () {
      hideTooltipCalled = true;
    };

    object.onKeyDown({ key: 'Esc' });

    expect(hideTooltipCalled).toBe(true);
    object.hideTooltip = keep;
  });

  it('ignores other key', () => {
    let hideTooltipCalled = false;
    const keep = object.hideTooltip;
    object.hideTooltip = function () {
      hideTooltipCalled = true;
    };

    object.onKeyDown({ key: 'Strg' });

    expect(hideTooltipCalled).toBe(false);
    object.hideTooltip = keep;
  });

  it('hides and calls blur on click somewhere', () => {
    let hideTooltipCalled = false;
    let blurCalled = false;
    const keep = object.hideTooltip;
    object.hideTooltip = function () {
      hideTooltipCalled = true;
    };
    element.blur = function () {
      blurCalled = true;
    };

    object.onPointerDown({});

    expect(hideTooltipCalled).toBe(true);
    expect(blurCalled).toBe(true);
    object.hideTooltip = keep;
  });

  it('does not hide on click on tooltip and prevents default', () => {
    let hideTooltipCalled = false;
    let preventDefaultCalled = false;
    const keep = object.hideTooltip;
    object.hideTooltip = function () {
      hideTooltipCalled = true;
    };
    const preventDefault = function () {
      preventDefaultCalled = true;
    };

    object.onPointerDown({ target: object.tooltip, preventDefault });

    expect(hideTooltipCalled).toBe(false);
    expect(preventDefaultCalled).toBe(true);
    object.hideTooltip = keep;
  });

  it('does not hide on click on element and prevents default', () => {
    let hideTooltipCalled = false;
    let preventDefaultCalled = false;
    const keep = object.hideTooltip;
    object.hideTooltip = function () {
      hideTooltipCalled = true;
    };
    const preventDefault = function () {
      preventDefaultCalled = true;
    };

    object.onPointerDown({ target: element, preventDefault });

    expect(hideTooltipCalled).toBe(false);
    expect(preventDefaultCalled).toBe(true);
    object.hideTooltip = keep;
  });
});

describe('tooltip is on top if there is not enough space below', () => {
  let classListAdd = [];
  let classListRemove = [];

  const window = {};

  const document = {
    addEventListener(ev, handler) {
    },
    removeEventListener(ev, handler) {
    },
    getElementById(which) {
      return tooltip;
    },
    getElementsByTagName(tag) {
      return [];
    },
    parentWindow: window,
  };

  const container = {
    addEventListener(ev, handler) {
    },
    classList: {
      add(which) {
        classListAdd.push(which);
      },
      remove(which) {
        classListRemove.push(which);
      },
    },
  };

  const element = {
    addEventListener(ev, handler) {
    },
    getAttribute(which) {
      return `attribute-${which}`;
    },
    ownerDocument: document,
    parentElement: container,
  };

  let clientRect = null;
  var tooltip = {
    getBoundingClientRect() {
      return clientRect;
    },
    style: {
      transform: null,
    },
  };

  it('does not add top-class if there is enough space', () => {
    clientRect = { bottom: 90 };
    window.innerHeight = 100;

    classListAdd = [];
    const object = new Tooltip(element);
    object.main = null;
    object.showTooltip();

    expect(classListAdd).toEqual(['c-tooltip--visible']);
  });

  it('does add top-class if there is not enough space', () => {
    clientRect = { bottom: 110 };
    window.innerHeight = 100;

    classListAdd = [];
    const object = new Tooltip(element);
    object.main = null;
    object.showTooltip();

    expect(classListAdd).toEqual(['c-tooltip--visible', 'c-tooltip--top']);
  });

  it('removes top-class when it hides', () => {
    const object = new Tooltip(element);
    object.main = null;

    classListRemove = [];
    object.hideTooltip();

    expect(classListRemove).toEqual(['c-tooltip--visible', 'c-tooltip--top']);
  });

  it('removes transform when it hides', () => {
    const object = new Tooltip(element);
    object.main = null;

    object.tooltip.style.transform = 'foo';
    object.hideTooltip();

    expect(object.tooltip.style.transform).toBeNull();
  });
});

describe('tooltip moves to left or right if there is not enough space', () => {
  const window = {};

  const document = {
    addEventListener(ev, handler) {
    },
    removeEventListener(ev, handler) {
    },
    getElementById(which) {
      return tooltip;
    },
    getElementsByTagName(tag) {
      return [];
    },
    parentWindow: window,
  };

  const container = {
    addEventListener(ev, handler) {
    },
    classList: {
      add(which) {
      },
      remove(which) {
      },
    },
  };

  const element = {
    addEventListener(ev, handler) {
    },
    getAttribute(which) {
      return `attribute-${which}`;
    },
    ownerDocument: document,
    parentElement: container,
  };

  let clientRect = null;
  var tooltip = {
    getBoundingClientRect() {
      return clientRect;
    },
    style: {
      transform: null,
    },
  };

  it('does not move if there is enough space', () => {
    clientRect = { left: 10, right: 20 };
    window.innerWidth = 100;

    const object = new Tooltip(element);
    object.main = null;
    object.showTooltip();

    expect(tooltip.style.transform).toBeNull();
  });

  it('does move to left if there is enough space', () => {
    clientRect = { left: 20, right: 110 };
    window.innerWidth = 100;

    const object = new Tooltip(element);
    object.main = null;
    object.showTooltip();

    expect(tooltip.style.transform).toBe('translateX(-10px)');
  });

  it('does move to right if there is enough space', () => {
    clientRect = { left: -10, right: 20, width: 40 };
    window.innerWidth = 100;

    const object = new Tooltip(element);
    object.main = null;
    object.showTooltip();

    expect(tooltip.style.transform).toBe('translateX(-10px)');
  });
});

describe('get display rect', () => {
  const window = {};

  const document = {
    addEventListener(ev, handler) {
    },
    removeEventListener(ev, handler) {
    },
    getElementById(which) {
      return tooltip;
    },
    getElementsByTagName(tag) {
      return [];
    },
    parentWindow: window,
  };

  const container = {
    addEventListener(ev, handler) {
    },
    classList: {
      add(which) {
      },
      remove(which) {
      },
    },
  };

  const element = {
    addEventListener(ev, handler) {
    },
    getAttribute(which) {
      return `attribute-${which}`;
    },
    ownerDocument: document,
    parentElement: container,
  };

  const clientRect = null;
  var tooltip = {
    getBoundingClientRect() {
      return clientRect;
    },
    style: {
      transform: null,
    },
  };

  it('returns window coordinates if tooltip is not in main', () => {
    const object = new Tooltip(element);

    window.innerWidth = 100;
    window.innerHeight = 150;

    const rect = object.getDisplayRect();

    expect(rect).toMatchObject({
      left: 0, top: 0, width: 100, height: 150,
    });
  });

  it('returns main coordinates if tooltip is in main', () => {
    const main = {
      contains(e) {
        return true;
      },
      getBoundingClientRect() {
        return {
          left: 10,
          top: 20,
          width: 110,
          height: 120,
        };
      },
    };
    element.ownerDocument.getElementsByTagName = function (w) {
      return [main];
    };

    const object = new Tooltip(element);
    const rect = object.getDisplayRect();

    expect(rect).toMatchObject({
      left: 10, top: 20, width: 110, height: 120,
    });
  });
});
