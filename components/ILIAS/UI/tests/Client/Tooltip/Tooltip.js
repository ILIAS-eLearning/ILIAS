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
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */

import { describe, it } from 'node:test';
import assert from 'node:assert/strict';
import * as CONSTANTS from '../../../resources/js/Tooltip/src/constants.js';
import Tooltip from '../../../resources/js/Tooltip/src/Tooltip.js';

/**
 * @param {Object} [opts]
 * @param {Function|Object} [opts.rect] rect or (classSet) => rect
 * @param {Function} [opts.contains]
 * @param {{innerWidth: number, innerHeight: number}} [opts.win]
 */
function makeElement({
  rect = {
    left: 0, top: 0, right: 0, bottom: 0, width: 0, height: 0,
  },
  contains = () => false,
  win = { innerWidth: 1000, innerHeight: 800 },
} = {}) {
  const classes = new Set();
  return {
    classes,
    classList: {
      add: (c) => classes.add(c),
      remove: (c) => classes.delete(c),
      contains: (c) => classes.has(c),
    },
    style: {},
    textContent: '',
    getBoundingClientRect: () => (typeof rect === 'function' ? rect(classes) : rect),
    contains,
    ownerDocument: { defaultView: win },
  };
}

const rect = (left, top, right, bottom) => ({
  left, top, right, bottom, width: right - left, height: bottom - top,
});

/**
 * Builds a tooltip whose bounding rect depends on whether the container
 * currently carries the "top" class (tooltip rendered above vs. below).
 */
function build({
  above = rect(400, 100, 500, 150),
  below = rect(400, 200, 500, 250),
  main = null,
  win = { innerWidth: 1000, innerHeight: 800 },
} = {}) {
  const container = makeElement();
  const tooltipEl = makeElement({
    win,
    rect: () => (container.classes.has(CONSTANTS.TOOLTIP_TOP_CLASS) ? above : below),
  });
  const target = makeElement();
  const content = makeElement();
  const tooltip = new Tooltip(target, container, tooltipEl, content, main);
  return {
    tooltip, target, container, tooltipEl, content,
  };
}

function makeMain(mainRect, containsTooltip = true) {
  return makeElement({ rect: mainRect, contains: () => containsTooltip });
}

const hasTop = (container) => container.classes.has(CONSTANTS.TOOLTIP_TOP_CLASS);

describe('Tooltip', () => {
  describe('getters', () => {
    it('returns the elements passed to the constructor', () => {
      const {
        tooltip, target, container, tooltipEl,
      } = build();
      assert.equal(tooltip.getTargetElement(), target);
      assert.equal(tooltip.getContainerElement(), container);
      assert.equal(tooltip.getTooltipElement(), tooltipEl);
    });
  });

  describe('setContent()', () => {
    it('sets the textContent of the content element', () => {
      const { tooltip, content } = build();
      tooltip.setContent('Hello');
      assert.equal(content.textContent, 'Hello');
    });

    it('does not interpret markup (uses textContent)', () => {
      const { tooltip, content } = build();
      tooltip.setContent('<b>bold</b>');
      assert.equal(content.textContent, '<b>bold</b>');
    });
  });

  describe('visibility', () => {
    it('is hidden initially', () => {
      const { tooltip } = build();
      assert.equal(tooltip.isVisible(), false);
      assert.equal(tooltip.isHidden(), true);
    });

    it('is visible after show()', () => {
      const { tooltip, container } = build();
      tooltip.show();
      assert.equal(tooltip.isVisible(), true);
      assert.equal(tooltip.isHidden(), false);
      assert.ok(container.classes.has(CONSTANTS.TOOLTIP_VISIBLE_CLASS));
    });

    it('is hidden again after hide()', () => {
      const { tooltip } = build();
      tooltip.show();
      tooltip.hide();
      assert.equal(tooltip.isVisible(), false);
      assert.equal(tooltip.isHidden(), true);
    });
  });

  describe('hide()', () => {
    it('removes the visible and top classes', () => {
      const { tooltip, container } = build();
      tooltip.show();
      assert.ok(hasTop(container));
      tooltip.hide();
      assert.ok(!container.classes.has(CONSTANTS.TOOLTIP_VISIBLE_CLASS));
      assert.ok(!hasTop(container));
    });

    it('resets the transform applied by horizontal correction', () => {
      const main = makeMain(rect(0, 0, 1000, 800));
      const { tooltip, tooltipEl } = build({
        above: rect(950, 100, 1050, 150),
        main,
      });
      tooltip.show();
      assert.ok(tooltipEl.style.transform);
      tooltip.hide();
      assert.equal(tooltipEl.style.transform, null);
    });

    it('is safe to call on a tooltip that was never shown', () => {
      const { tooltip } = build();
      assert.doesNotThrow(() => tooltip.hide());
    });
  });

  describe('show() – vertical placement', () => {
    const mainRect = rect(0, 0, 1000, 800);

    it('prefers above the target when it fits', () => {
      const { tooltip, container } = build({
        above: rect(400, 100, 500, 150),
        main: makeMain(mainRect),
      });
      tooltip.show();
      assert.ok(hasTop(container));
    });

    it('treats exactly touching the top edge as fitting', () => {
      const { tooltip, container } = build({
        above: rect(400, 0, 500, 50), // overflowTop === 0
        main: makeMain(mainRect),
      });
      tooltip.show();
      assert.ok(hasTop(container));
    });

    it('falls back to below when above overflows and below fits', () => {
      const { tooltip, container } = build({
        above: rect(400, -20, 500, 30), // overflowTop = 20
        below: rect(400, 60, 500, 110), // overflowBottom = -690
        main: makeMain(mainRect),
      });
      tooltip.show();
      assert.ok(!hasTop(container));
    });

    it('chooses above when both overflow and below overflows more', () => {
      const { tooltip, container } = build({
        above: rect(400, -10, 500, 40), // overflowTop = 10
        below: rect(400, 790, 500, 850), // overflowBottom = 50
        main: makeMain(mainRect),
      });
      tooltip.show();
      assert.ok(hasTop(container));
    });

    it('chooses below when both overflow and above overflows more', () => {
      const { tooltip, container } = build({
        above: rect(400, -100, 500, -50), // overflowTop = 100
        below: rect(400, 790, 500, 810), // overflowBottom = 10
        main: makeMain(mainRect),
      });
      tooltip.show();
      assert.ok(!hasTop(container));
    });

    it('respects the main element bounds rather than the window', () => {
      // window is 800 high, but main starts at top = 200
      const { tooltip, container } = build({
        above: rect(400, 150, 500, 200), // overflowTop = 50 relative to main
        below: rect(400, 260, 500, 310),
        main: makeMain(rect(0, 200, 1000, 600)),
      });
      tooltip.show();
      assert.ok(!hasTop(container));
    });
  });

  describe('show() – horizontal placement', () => {
    const mainRect = rect(0, 0, 1000, 800);

    it('does not set a transform when the tooltip fits', () => {
      const { tooltip, tooltipEl } = build({
        above: rect(400, 100, 500, 150),
        main: makeMain(mainRect),
      });
      tooltip.show();
      assert.equal(tooltipEl.style.transform, undefined);
    });

    it('shifts left when overflowing the right edge', () => {
      const { tooltip, tooltipEl } = build({
        above: rect(950, 100, 1050, 150),
        main: makeMain(mainRect),
      });
      tooltip.show();
      assert.equal(tooltipEl.style.transform, 'translateX(calc(-50% + -50px))');
    });

    it('shifts right when overflowing the left edge', () => {
      const { tooltip, tooltipEl } = build({
        above: rect(-30, 100, 70, 150),
        main: makeMain(mainRect),
      });
      tooltip.show();
      assert.equal(tooltipEl.style.transform, 'translateX(calc(-50% + 30px))');
    });

    it('does not shift when exactly touching the edges', () => {
      const { tooltip, tooltipEl } = build({
        above: rect(0, 100, 1000, 150),
        main: makeMain(mainRect),
      });
      tooltip.show();
      assert.equal(tooltipEl.style.transform, undefined);
    });

    it('measures horizontally using the placement chosen vertically', () => {
      // above overflows top, so tooltip ends up below, where it overflows right
      const { tooltip, tooltipEl } = build({
        above: rect(400, -20, 500, 30),
        below: rect(960, 60, 1060, 110),
        main: makeMain(mainRect),
      });
      tooltip.show();
      assert.equal(tooltipEl.style.transform, 'translateX(calc(-50% + -60px))');
    });
  });

  describe('display rect selection', () => {
    it('uses the main element when it contains the tooltip', () => {
      const { tooltip, tooltipEl } = build({
        above: rect(550, 100, 650, 150),
        main: makeMain(rect(0, 0, 600, 800), true),
      });
      tooltip.show();
      assert.equal(tooltipEl.style.transform, 'translateX(calc(-50% + -50px))');
    });

    it('ignores the main element when it does not contain the tooltip', () => {
      // main is narrow, but because it does not contain the tooltip
      // the (wider) window bounds apply and nothing overflows
      const { tooltip, tooltipEl } = build({
        above: rect(550, 100, 650, 150),
        main: makeMain(rect(0, 0, 600, 800), false),
        win: { innerWidth: 1000, innerHeight: 800 },
      });
      tooltip.show();
      assert.equal(tooltipEl.style.transform, undefined);
    });

    it('falls back to window bounds when there is no main element', () => {
      const { tooltip, tooltipEl } = build({
        above: rect(-40, 100, 60, 150),
        main: null,
      });
      tooltip.show();
      assert.equal(tooltipEl.style.transform, 'translateX(calc(-50% + 40px))');
    });

    it('uses the tooltip document window dimensions for the fallback', () => {
      const { tooltip, container } = build({
        above: rect(400, 100, 500, 150),
        main: null,
        win: { innerWidth: 1000, innerHeight: 800 },
      });
      tooltip.show();
      assert.ok(hasTop(container));
    });

    // NOTE: #getDisplayRect()'s window fallback returns only
    // {left, top, width, height} – there is no `right` / `bottom`.
    // The two tests below describe the intended behaviour and FAIL
    // against the current implementation (comparisons against undefined
    // are always false). Fix: add `right: innerWidth, bottom: innerHeight`.
    it('window fallback: shifts left when overflowing the right edge', () => {
      const { tooltip, tooltipEl } = build({
        above: rect(950, 100, 1050, 150),
        main: null,
        win: { innerWidth: 1000, innerHeight: 800 },
      });
      tooltip.show();
      assert.equal(tooltipEl.style.transform, 'translateX(calc(-50% + -50px))');
    });

    it('window fallback: picks the side that overflows less at the bottom', () => {
      const { tooltip, container } = build({
        above: rect(400, -50, 500, 50), // overflowTop = 50
        below: rect(400, 800, 500, 900), // overflowBottom = 100 → above wins
        main: null,
        win: { innerWidth: 1000, innerHeight: 800 },
      });
      tooltip.show();
      assert.ok(hasTop(container));
    });
  });
});
