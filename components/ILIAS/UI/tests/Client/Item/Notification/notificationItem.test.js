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
import { notificationItemFactory, notificationItemObject } from '../../../../resources/js/Item/src/notification.main';
import { counterFactory } from '../../../../resources/js/Counter/src/counter.main';

// import { JSDOM } from 'jsdom';
// import fs from 'fs';

// const test_dom_string = fs.readFileSync(
//    './components/ILIAS/UI/tests/Client/Item/Notification/NotificationItemTest.html'
// ).toString();
// const test_document = new JSDOM(test_dom_string);
// const $ = global.jQuery = require('jquery')(test_document.window);

const getNotificationItemTest1 = function ($, counterFactory) {
  return notificationItemFactory($, counterFactory).getNotificationItemObject($('#id_2'));
};
const getNotificationItemTest2 = function ($, counterFactory) {
  return notificationItemFactory($, counterFactory).getNotificationItemObject($('#id_6'));
};
const getNotificationItemAggregate = function ($, counterFactory) {
  return notificationItemFactory($, counterFactory).getNotificationItemObject($('#id_4'));
};

describe('Notification Item Factory', () => {
  it('Notification Item Factory is here', () => {
    expect(notificationItemFactory).toBeDefined();
  });

  it('Notification Item Object is here', () => {
    expect(notificationItemObject).toBeDefined();
  });

  it.skip('Get Valid Object', () => {
    expect(notificationItemObject).toBeDefined();
    expect(getNotificationItemTest1($, counterFactory)).not.toBeInstanceOf(jQuery);
  });
});

describe.skip('Notification Item Object', () => {
  it('Get Close Button 1', () => {
    const $button = getNotificationItemTest1($, counterFactory).getCloseButtonOfItem();
    expect($button.attr('id')).toBe('id_3');
  });
  it('Get Counter if Any', () => {
    const $counter = getNotificationItemTest1($, counterFactory).getCounterObjectIfAny();
    expect($counter.getNoveltyCount()).toBe(2);
    expect($counter.getStatusCount()).toBe(0);
  });

  it('Set/Get Item Description of item with existing description', () => {
    const item = getNotificationItemTest2($, counterFactory);
    expect(item.getItemDescription()).toBe('Existing Description');
    expect(item.setItemDescription('Test Description 1').getItemDescription()).toBe('Test Description 1');
  });

  it('Set/Get Item Description of item without existing description', () => {
    const item = getNotificationItemTest1($, counterFactory);
    expect(item.getItemDescription()).toBe('');
    const fail = () => item.setItemDescription('This will Fail');
    expect(fail).toThrow('No Description Field in DOM for given Notification Item');
  });

  it('Set/Get Item Properties of item with existing properties', () => {
    const item = getNotificationItemTest2($, counterFactory);
    expect(item.getItemPropertyValueAtPosition(1)).toBe('Property Value 1');
    expect(item.setItemPropertyValueAtPosition('Test Property 1', 1)
      .getItemPropertyValueAtPosition(1)).toBe('Test Property 1');
  });

  it(
    'Set/Get Item Properties of item with non-existing position or field',
    () => {
      const item = getNotificationItemTest2($, counterFactory);
      const fail1 = () => item.getItemPropertyValueAtPosition(3);
      expect(fail1).toThrow('No property with position 3 doest not exist for given Notification Item');

      const fail2 = () => getNotificationItemTest1($, counterFactory).getItemPropertyValueAtPosition(3);
      expect(fail2).toThrow('No properties exist for in DOM for given Notification Item');
    }
  );

  it('Remove Properties from field', () => {
    const item = getNotificationItemTest2($, counterFactory);

    expect(item.setItemPropertyValueAtPosition('Test Property 1', 1)
      .getItemPropertyValueAtPosition(1)).toBe('Test Property 1');
    item.removeItemProperties();

    const fail = () => item.getItemPropertyValueAtPosition(1);
    expect(fail).toThrow('No properties exist for in DOM for given Notification Item');
  });

  it('has Sibblings', () => {
    expect(getNotificationItemTest2($, counterFactory).hasSibblings()).toBe(true);
    expect(getNotificationItemAggregate($, counterFactory).hasSibblings()).toBe(false);
  });

  it('get nr Of Sibblings', () => {
    expect(getNotificationItemTest2($, counterFactory).getNrOfSibblings()).toBe(1);
    expect(getNotificationItemAggregate($, counterFactory).getNrOfSibblings()).toBe(0);
  });

  it('get Parent Item', () => {
    expect(getNotificationItemTest2($, counterFactory).getParentItem()).toBe(false);
    const expected_item = getNotificationItemTest2($, counterFactory);
    expect(getNotificationItemAggregate($, counterFactory).getParentItem().getNrOfSibblings()).toBe(1);
  });

  it('is Aggregate', () => {
    expect(getNotificationItemTest2($, counterFactory).isAggregate()).toBe(false);
    expect(getNotificationItemAggregate($, counterFactory).isAggregate()).toBe(true);
  });

  // Note this needs to stay executed last, since it removes item 1 permanently from the DOM.
  it('Remove one Item', () => {
    expect($('#id_2').html()).toBeDefined();
    getNotificationItemTest1($, counterFactory).closeItem(1);
    expect($('#id_2').html()).toBeUndefined();

    const $counter = getNotificationItemTest2($, counterFactory).getCounterObjectIfAny();
    expect($counter.getNoveltyCount()).toBe(1);
    expect($counter.getStatusCount()).toBe(0);
  });
});
