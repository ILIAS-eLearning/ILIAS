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
import { counterFactory, counterObject } from '../../../resources/js/Counter/src/counter.main';

// import { JSDOM } from 'jsdom';
// import fs from 'fs';

// const test_dom_string = fs.readFileSync(
//    './components/ILIAS/UI/tests/Client/Counter/CounterTest.html'
// ).toString();
// const test_document = new JSDOM(test_dom_string);
// const $ = global.jQuery = require('jquery')(test_document.window);

const getCounterTest1 = function ($) {
  return counterFactory($).getCounterObject($('#test1'));
};
const getCounterTest2 = function ($) {
  return counterFactory($).getCounterObject($('#test2'));
};

describe('Counter Factory', () => {
  it('Counter Factory is here', () => {
    expect(counterFactory).toBeDefined();
  });

  it.skip('getCounterObjectOrNull On Empty', () => {
    expect(counterFactory($).getCounterObjectOrNull($('#testEmpty'))).toBeNull();
  });
  it('Counter Object is here', () => {
    expect(counterObject).toBeDefined();
  });

  it.skip('Get Valid Object', () => {
    expect(getCounterTest1($)).toBeDefined();
    expect(getCounterTest1($)).not.toBeInstanceOf(jQuery);
  });
});
describe.skip('Counter Object', () => {
  it('Get Valid Counts', () => {
    expect(getCounterTest1($).getStatusCount()).toBe(1);
    expect(getCounterTest1($).getNoveltyCount()).toBe(5);
  });
  it(
    'Get Valid Counts Test Setters with Div containing two counter Objectgs, which are summed up',
    () => {
      expect(getCounterTest2($).getStatusCount()).toBe(2);
      expect(getCounterTest2($).getNoveltyCount()).toBe(10);
    }
  );

  it('Test Setters', () => {
    const ctest1 = getCounterTest1($);
    expect(ctest1.setStatusTo(2).getStatusCount()).toBe(2);
    expect(ctest1.getNoveltyCount()).toBe(5);

    expect(ctest1.setNoveltyTo(7).getStatusCount()).toBe(2);
    expect(ctest1.getNoveltyCount()).toBe(7);
  });
  it(
    'Test Setters with Div containing two counter Objectgs, which are summed up',
    () => {
      const ctest2 = getCounterTest2($);
      expect(ctest2.setStatusTo(2).getStatusCount()).toBe(4);
      expect(ctest2.getNoveltyCount()).toBe(10);

      expect(ctest2.setNoveltyTo(7).getStatusCount()).toBe(4);
      expect(ctest2.getNoveltyCount()).toBe(14);
    }
  );

  it('Test Increment', () => {
    expect(getCounterTest1($).setNoveltyTo(3).incrementNoveltyCount(2).getNoveltyCount()).toBe(5);
    expect(getCounterTest1($).setStatusTo(3).incrementStatusCount(2).getStatusCount()).toBe(5);

    expect(getCounterTest2($).setNoveltyTo(3).incrementNoveltyCount(2).getNoveltyCount()).toBe(10);
    expect(getCounterTest2($).setStatusTo(3).incrementStatusCount(2).getStatusCount()).toBe(10);
  });

  it('Test Decrement', () => {
    expect(getCounterTest1($).setNoveltyTo(3).decrementNoveltyCount(2).getNoveltyCount()).toBe(1);
    expect(getCounterTest1($).setStatusTo(3).decrementStatusCount(2).getStatusCount()).toBe(1);

    expect(getCounterTest2($).setNoveltyTo(3).decrementNoveltyCount(2).getNoveltyCount()).toBe(2);
    expect(getCounterTest2($).setStatusTo(3).decrementStatusCount(2).getStatusCount()).toBe(2);
  });

  it('Test Decrement', () => {
    expect(getCounterTest1($).setNoveltyTo(3).decrementNoveltyCount(2).getNoveltyCount()).toBe(1);
    expect(getCounterTest1($).setStatusTo(3).decrementStatusCount(2).getStatusCount()).toBe(1);
  });

  it('Test Get Novelty To Status', () => {
    var counter = getCounterTest1($).setStatusTo(3).setNoveltyTo(2).setTotalNoveltyToStatusCount();

    expect(counter.getStatusCount()).toBe(5);
    expect(counter.getNoveltyCount()).toBe(0);

    var counter = getCounterTest2($).setStatusTo(3).setNoveltyTo(2).setTotalNoveltyToStatusCount();
    expect(counter.getStatusCount()).toBe(14);
    expect(counter.getNoveltyCount()).toBe(0);
  });
});
