import { expect } from 'chai';
import { JSDOM } from 'jsdom';
import fs from 'fs';

import { notificationItemFactory, notificationItemObject } from '../../../../src/templates/js/Item/src/notification.main';
import { counterFactory } from '../../../../src/templates/js/Counter/src/counter.main';

const test_dom_string = fs.readFileSync('./components/ILIAS/UI/test/Client/Item/Notification/NotificationItemTest.html').toString();
const test_document = new JSDOM(test_dom_string);
const $ = global.jQuery = require('jquery')(test_document.window);

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
    expect(notificationItemFactory).to.not.be.undefined;
  });

  it('Notification Item Object is here', () => {
    expect(notificationItemObject).to.not.be.undefined;
  });

  it('Get Valid Object', () => {
    expect(notificationItemObject).to.not.be.undefined;
    expect(getNotificationItemTest1($, counterFactory)).not.to.be.instanceOf(jQuery);
  });
});

describe('Notification Item Object', () => {
  it('Get Close Button 1', () => {
    const $button = getNotificationItemTest1($, counterFactory).getCloseButtonOfItem();
    expect($button.attr('id')).to.be.equal('id_3');
  });
  it('Get Counter if Any', () => {
    const $counter = getNotificationItemTest1($, counterFactory).getCounterObjectIfAny();
    expect($counter.getNoveltyCount()).to.be.equal(2);
    expect($counter.getStatusCount()).to.be.equal(0);
  });

  it('Set/Get Item Description of item with existing description', () => {
    const item = getNotificationItemTest2($, counterFactory);
    expect(item.getItemDescription()).to.be.equal('Existing Description');
    expect(item.setItemDescription('Test Description 1').getItemDescription()).to.be.equal('Test Description 1');
  });

  it('Set/Get Item Description of item without existing description', () => {
    const item = getNotificationItemTest1($, counterFactory);
    expect(item.getItemDescription()).to.be.equal('');
    const fail = () => item.setItemDescription('This will Fail');
    expect(fail).to.throw('No Description Field in DOM for given Notification Item');
  });

  it('Set/Get Item Properties of item with existing properties', () => {
    const item = getNotificationItemTest2($, counterFactory);
    expect(item.getItemPropertyValueAtPosition(1)).to.be.equal('Property Value 1');
    expect(item.setItemPropertyValueAtPosition('Test Property 1', 1)
      .getItemPropertyValueAtPosition(1)).to.be.equal('Test Property 1');
  });

  it('Set/Get Item Properties of item with non-existing position or field', () => {
    const item = getNotificationItemTest2($, counterFactory);
    const fail1 = () => item.getItemPropertyValueAtPosition(3);
    expect(fail1).to.throw('No property with position 3 doest not exist for given Notification Item');

    const fail2 = () => getNotificationItemTest1($, counterFactory).getItemPropertyValueAtPosition(3);
    expect(fail2).to.throw('No properties exist for in DOM for given Notification Item');
  });

  it('Remove Properties from field', () => {
    const item = getNotificationItemTest2($, counterFactory);

    expect(item.setItemPropertyValueAtPosition('Test Property 1', 1)
      .getItemPropertyValueAtPosition(1)).to.be.equal('Test Property 1');
    item.removeItemProperties();

    const fail = () => item.getItemPropertyValueAtPosition(1);
    expect(fail).to.throw('No properties exist for in DOM for given Notification Item');
  });

  it('has Sibblings', () => {
    expect(getNotificationItemTest2($, counterFactory).hasSibblings()).to.be.equal(true);
    expect(getNotificationItemAggregate($, counterFactory).hasSibblings()).to.be.equal(false);
  });

  it('get nr Of Sibblings', () => {
    expect(getNotificationItemTest2($, counterFactory).getNrOfSibblings()).to.be.equal(1);
    expect(getNotificationItemAggregate($, counterFactory).getNrOfSibblings()).to.be.equal(0);
  });

  it('get Parent Item', () => {
    expect(getNotificationItemTest2($, counterFactory).getParentItem()).to.be.equal(false);
    const expected_item = getNotificationItemTest2($, counterFactory);
    expect(getNotificationItemAggregate($, counterFactory).getParentItem().getNrOfSibblings()).to.be.equal(1);
  });

  it('is Aggregate', () => {
    expect(getNotificationItemTest2($, counterFactory).isAggregate()).to.be.equal(false);
    expect(getNotificationItemAggregate($, counterFactory).isAggregate()).to.be.equal(true);
  });

  // Note this needs to stay executed last, since it removes item 1 permanently from the DOM.
  it('Remove one Item', () => {
    expect($('#id_2').html()).to.not.be.undefined;
    getNotificationItemTest1($, counterFactory).closeItem(1);
    expect($('#id_2').html()).to.be.undefined;

    const $counter = getNotificationItemTest2($, counterFactory).getCounterObjectIfAny();
    expect($counter.getNoveltyCount()).to.be.equal(1);
    expect($counter.getStatusCount()).to.be.equal(0);
  });
});
