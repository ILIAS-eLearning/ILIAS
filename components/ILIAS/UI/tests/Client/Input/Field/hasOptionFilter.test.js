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
 * @author Ferdinand Engländer <ferdinand.englaender@concepts-and-training.de>
 */

import {
  beforeEach, afterEach, describe, it,
} from 'node:test';
import { strict } from 'node:assert/strict';
import InputHasOptionFilterContextFactory
  from '../../../../resources/js/Input/Field/src/hasQuickFilterContext/hasOptionFilter.factory.js';

describe('InputHasOptionFilterContext', () => {
  let listeners;
  let elementMock;
  const someId = 'someId';
  let component;
  let factory;
  let removePropertyCallCount = 0;
  let addCalled;
  let removeCalled;
  let setAttrCalled;

  beforeEach(() => {
    removePropertyCallCount = 0;
    addCalled = 0;
    removeCalled = 0;
    setAttrCalled = 0;

    // addEventListener capture map
    listeners = {};
    const captureAddEventListener = (type, cb) => {
      if (!listeners[type]) listeners[type] = [];
      listeners[type].push(cb);
    };

    global.window = global.window || {};
    // eslint-disable-next-line no-unused-vars
    global.window.setTimeout = (cb, ms) => { cb(); return 1; };
    // eslint-disable-next-line no-unused-vars
    global.window.clearTimeout = (id) => {};
    global.requestAnimationFrame = (cb) => { cb(); return 1; };

    elementMock = {
      querySelectorAll() {
        return [];
      },
      querySelector() {
        return this;
      },
      closest() {
        return this;
      },
      hasAttribute: () => true,
      addEventListener: captureAddEventListener,
      getAttribute: () => 'radio-field-input',
      id: someId,
      style: {
        removeProperty: () => {
          removePropertyCallCount += 1;
        },
        display: '',
      },
      classList: {
        add: () => {
          addCalled += 1;
        },
        remove: () => {
          removeCalled += 1;
        },
      },
      setAttribute: () => {
        setAttrCalled += 1;
      },
      innerHTML: '%s results',
      textContent: '',
    };

    factory = new InputHasOptionFilterContextFactory();
    component = factory.init(elementMock);
  });

  afterEach(() => {
    delete global.window;
    delete global.requestAnimationFrame;
  });

  it('setIsFiltered method can flip between filtered and unfiltered visual state', () => {
    // getIsFiltered should always reflect the state that was set
    // flipping to true triggers the unhiding of the clearFilterButton through removeProperty()
    // initial
    strict.equal(component.getIsFiltered(), false);

    // flip to true
    component.setIsFiltered(true); // calls removeProperty 2 times
    strict.equal(component.getIsFiltered(), true);
    strict.equal(removePropertyCallCount, 2, 'property must have been called 1 because of setFiltered(true)'); // was unhiding of clearFilterButton requested?

    // deactivate filtered state
    component.setIsFiltered(false);
    strict.equal(component.getIsFiltered(), false);
  });

  it('filterItemSearch items found by filter are turned visible', () => {
    // count if showItem() was triggered because the item.textContent matches the searchEvent

    let matchFound = 0;
    const searchEvent = {
      target: {
        value: 'foo',
      },
    };
    component.items = [
      {
        textContent: 'foo',
        style: {
          removeProperty: () => {
            matchFound += 1; // should count
          },
        },
      },
      {
        textContent: 'bar',
        style: {
          removeProperty: () => {
            matchFound += 1; // should NOT count
          },
        },
      },
    ];
    component.filterItemsSearch(searchEvent);
    strict.equal(matchFound, 1);
  });

  it('toggleVisibility flips visual style from collapsed to expanded', () => {
    // initial state
    strict.equal(component.isEngaged, false);

    // expand
    component.toggleVisibility();
    strict.equal(component.isEngaged, true);
    strict.equal(addCalled, 1);
    strict.equal(setAttrCalled, 1);

    // collapse
    component.toggleVisibility();
    strict.equal(component.isEngaged, false);
    strict.equal(removeCalled, 1);
  });

  it('eventListener should call setIsFiltered(false) when clearFilterButton is clicked', () => {
    strict.ok(listeners.click && listeners.click.length > 0, 'click listeners registered');
    component.setIsFiltered(true); // simulating a filtered state
    listeners.click.forEach((cb) => cb({})); // click on clearFilter btn should reset to unfiltered
    strict.equal(component.getIsFiltered(), false);
  });

  it('eventListener should call toggleVisibility() when expand/collapse button is clicked', () => {
    strict.ok(listeners.click && listeners.click.length > 0, 'click listeners registered');
    strict.equal(component.isEngaged, false);
    listeners.click.forEach((cb) => cb({})); // first click flips to true
    strict.equal(component.isEngaged, true);
    listeners.click.forEach((cb) => cb({})); // second click flips to false
    strict.equal(component.isEngaged, false);
  });

  it('resultDisplay for screen reader fills translation string correctly', () => {
    const searchEvent = {
      target: {
        value: 'foo',
      },
    };
    component.items = [
      {
        textContent: 'foo',
        style: {
          removeProperty: () => {
          },
        },
      },
      {
        textContent: 'food',
        style: {
          removeProperty: () => {
          },
        },
      },
      {
        textContent: 'bar',
        style: {
          removeProperty: () => {
          },
        },
      },
    ];

    component.filterItemsSearch(searchEvent);
    strict.equal(component.resultCountDisplay.textContent, '2 results');
  });
});
