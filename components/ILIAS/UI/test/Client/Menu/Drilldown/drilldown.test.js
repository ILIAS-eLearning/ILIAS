import { expect } from 'chai';
import { JSDOM } from 'jsdom';
import fs from 'fs';

import ddmodel from '../../../../src/templates/js/Menu/src/drilldown.model.js';
import ddmapping from '../../../../src/templates/js/Menu/src/drilldown.mapping.js';
import ddpersistence from '../../../../src/templates/js/Menu/src/drilldown.persistence.js';
import dd from '../../../../src/templates/js/Menu/src/drilldown.main.js';
import drilldown from '../../../../src/templates/js/Menu/src/drilldown.instances.js';

describe('drilldown', () => {
  beforeEach(() => {
    // init test environment
    const dom_string = fs.readFileSync('./components/ILIAS/UI/test/Component/Menu/Drilldown/drilldown_test.html').toString();
    const doc = new JSDOM(dom_string);

    doc.getElementById = (id) => $(`#${id}`)[0];
    global.document = doc;
    global.jQuery = require('jquery')(doc.window);
    global.$ = global.jQuery;

    il = {
      Utilities: {
        CookieStorage(id) {
          return {
            items: {},
            add(key, value) {
              this.items[key] = value;
            },
            store() {},
          };
        },
      },
    };
  });

  it('components are defined and provide public interface', () => {
    expect(ddmodel).to.not.be.undefined;
    expect(ddmapping).to.not.be.undefined;
    expect(ddpersistence).to.not.be.undefined;
    expect(drilldown).to.not.be.undefined;
    expect(dd).to.not.be.undefined;

    const ddmain = dd();
    expect(ddmain.init).to.be.an('function');
    expect(ddmain.engage).to.be.an('function');
  });

  it('model creates levels, engages/disengages properly', () => {
    const dd_model = ddmodel();
    const l0 = dd_model.actions.addLevel('root');
    const l1 = dd_model.actions.addLevel('1', l0.id);
    const l11 = dd_model.actions.addLevel('11', l1.id);
    const l2 = dd_model.actions.addLevel('2', l0.id);
    const l21 = dd_model.actions.addLevel('21', l2.id);
    const l211 = dd_model.actions.addLevel('211', l21.id);

    expect(l0.label).to.eql('root');
    expect(l0.id).to.eql('0');
    expect(l0.parent).to.eql(null);

    expect(l11.label).to.eql('11');
    expect(l11.id).to.eql('2');
    expect(l11.parent).to.eql('1');

    expect(dd_model.actions.getCurrent()).to.eql(l0);
    expect(l1.engaged).to.be.false;

    dd_model.actions.engageLevel(l1.id);
    expect(l1.engaged).to.be.true;
    expect(dd_model.actions.getCurrent()).to.eql(l1);

    dd_model.actions.engageLevel(l211.id);
    expect(l1.engaged).to.be.false;
    expect(l211.engaged).to.be.true;
    expect(dd_model.actions.getCurrent()).to.eql(l211);
    dd_model.actions.upLevel();
    expect(l21.engaged).to.be.true;
    expect(l211.engaged).to.be.false;
  });

  it('identifies several instances', () => {
    const id = 'dd_one';
    const id2 = 'dd_two';
    const mock = function () {};
    const ddmock = function (a, b, c) {
      return { init() {} };
    };
    const dd_collection = drilldown(mock, mock, mock, ddmock);

    dd_collection.init(id);
    expect(dd_collection.instances[id]).to.not.be.undefined;

    dd_collection.init(id2);
    expect(dd_collection.instances[id2]).to.not.equal(dd_collection.instances[id]);
  });

  it('persistence has internal integrity', () => {
    const p = ddpersistence('id');
    const value = 'test';
    p.store(value);
    expect(p.read()).to.equal(value);
  });

  it('parses, initializes and engages (dom level) ', () => {
    const component = drilldown(
      ddmodel,
      ddmapping,
      ddpersistence,
      dd,
    );
    const id = 'id_2';
    const signal = 'test_backsignal_id_2';
    const persistence_id = 'id_2_cookie';
    let menu;
    const btns = $('.il-drilldown ul li button');

    component.init(id, signal, persistence_id);
    menu = component.instances[id];
    expect(menu).to.be.an('object');

    expect($('header h2').html()).to.equal('root');
    expect(btns[1].className).to.equal('menulevel');

    btns[1].click();
    expect($('header h2').html()).to.equal('1');
    expect(btns[1].className).to.equal('menulevel engaged');

    btns[3].click();
    expect($('header h2').html()).to.equal('1.2');
    expect(btns[1].className).to.equal('menulevel');
    expect(btns[3].className).to.equal('menulevel engaged');
  });
});
