import { assert, expect } from 'chai';
import { JSDOM } from 'jsdom';
import fs from 'fs';

import Drilldown from '../../../../resources/js/Menu/src/drilldown.main';
import DrilldownFactory from '../../../../resources/js/Menu/src/drilldown.factory';
import DrilldownPersistence from '../../../../resources/js/Menu/src/drilldown.persistence';
import DrilldownModel from '../../../../resources/js/Menu/src/drilldown.model';
import DrilldownMapping from '../../../../resources/js/Menu/src/drilldown.mapping';

class ResizeObserverMock {
  observe() {}
  unobserve() {}
  disconnect() {}
}

const parsedHtml = `<section class="c-drilldown" id="id_2">
    <header class="">
        <div></div>
        <div></div>
        <div class="c-drilldown__filter">
            <label for="id_3" class="control-label">filter_nodes_in</label>
            <input id="id_3" type="text" name="" class="form-control">
        </div>
        <div class="c-drilldown__backnav">
            <button class="btn btn-bulky" id="id_1" aria-label="back">
                <span class="glyph" role="img">
                    <span class="glyphicon glyphicon-triangle-left" aria-hidden="true"></span>
                </span>
                <span class="bulky-label"></span>
            </button>
        </div>
    </header>
    <div class="c-drilldown__menu">
        <ul aria-live="polite" aria-label="root" data-ddindex="0" class="c-drilldown__menulevel--engaged c-drilldown__menulevel--engagedparent">
            <li class="c-drilldown__branch">
                <button class="c-drilldown__menulevel--trigger" aria-expanded="false">1
                    <span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span>
                </button>
                <ul data-ddindex="1">
                    <li class="c-drilldown__branch">
                        <button class="c-drilldown__menulevel--trigger" aria-expanded="false">1.1
                            <span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span>
                        </button>
                        <ul data-ddindex="2"></ul>
                    </li>
                    <li class="c-drilldown__branch">
                        <button class="c-drilldown__menulevel--trigger" aria-expanded="false">1.2
                            <span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span>
                        </button>
                        <ul data-ddindex="3"></ul>
                    </li>
                </ul>
            </li>
            <li class="c-drilldown__branch">
                <button class="c-drilldown__menulevel--trigger" aria-expanded="false">2
                    <span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span>
                </button>
                <ul data-ddindex="4"></ul>
            </li>
        </ul>
    </div>
</section>
`;

function buildDocument() {
  const dom_string = fs.readFileSync('./components/ILIAS/UI/tests/Component/Menu/Drilldown/drilldown_test.html').toString();
  const dom = new JSDOM(
    dom_string,
    {
      url: 'https://localhost',
    }
  );

  return dom.window.document;
}

function buildFactory(doc) {
  const jquery = (string) => {
    return {
      on(event, handler) {
        return;
      }
    };
  };

  const il = {
    Utilities: {
      CookieStorage(id) {
        return {
          items: {},
          add(key, value) {
            this.items[key] = value;
          },
          store() {}
        };
      }
    }
  };

  return new DrilldownFactory(doc, ResizeObserverMock, jquery, il);
}

describe('Drilldown', () => {
  it('classes exist', () => {
    expect(Drilldown).to.not.be.undefined;
    expect(DrilldownFactory).to.not.be.undefined;
    expect(DrilldownPersistence).to.not.be.undefined;
    expect(DrilldownModel).to.not.be.undefined;
    expect(DrilldownMapping).to.not.be.undefined;
  });
  it('factory has public methods', () => {
    const f = buildFactory(buildDocument());
    expect(f.init).to.be.an('function');
  });
  it('dom is correct after init', () => {
    const doc = buildDocument();
    const f = buildFactory(doc);
    f.init('id_2', () => { return; }, 'id_2');
    assert.equal(doc.body.innerHTML, parsedHtml);
  });
  it('buildLeaf returns correct leaf object', () => {
    const model = new DrilldownModel();
    assert.deepEqual(model.buildLeaf('1', 'My Leaf'), {index: '1', text: 'My Leaf', filtered: false});
  });
  it('addLevel returns correct level object', () => {
    const document = buildDocument();
    const model = new DrilldownModel();
    const leaves = [
      model.buildLeaf('1', 'My first Leaf'),
      model.buildLeaf('2', 'My second Leaf'),
      model.buildLeaf('3', 'My third Leaf')
    ];
    assert.deepEqual(
      model.addLevel(document.querySelector('.c-drilldown__menulevel--trigger'), null, leaves),
      {
        id: '0',
        parent: null,
        engaged: false,
        headerDisplayElement: document.querySelector('.c-drilldown__menulevel--trigger'),
        leaves: [
          { index: '1', text: 'My first Leaf', filtered: false },
          { index: '2', text: 'My second Leaf', filtered: false },
          { index: '3', text: 'My third Leaf', filtered: false }
        ]
      }
    );
  });
  it('getCurrent returns engaged', () => {
    const document = buildDocument();
    const model = new DrilldownModel();
    model.addLevel(document.querySelector('.c-drilldown__menulevel--trigger'), null, []),
    model.addLevel(document.querySelector('.c-drilldown__menulevel--trigger'), '0', []),
    model.addLevel(document.querySelector('.c-drilldown__menulevel--trigger'), '0', []),
    model.engageLevel('1');
    assert.equal(model.getCurrent().id, '1');
    model.upLevel();
    assert.equal(model.getCurrent().id, '0');
  });
  it('upLevel moves level up', () => {
    const document = buildDocument();
    const model = new DrilldownModel();
    model.addLevel(document.querySelector('.c-drilldown__menulevel--trigger'), null, []),
    model.addLevel(document.querySelector('.c-drilldown__menulevel--trigger'), '0', []),
    model.addLevel(document.querySelector('.c-drilldown__menulevel--trigger'), '1', []),
    model.engageLevel('2');
    model.upLevel();
    assert.equal(model.getCurrent().id, '1');
    model.upLevel();
    assert.equal(model.getCurrent().id, '0');
  });
  it('filtered and get filtered work as expected', () => {
    const document = buildDocument();
    const model = new DrilldownModel();
    model.addLevel(document.querySelector('.c-drilldown__menulevel--trigger'), null, []),
    model.addLevel(document.querySelector('.c-drilldown__menulevel--trigger'), null, []),
    model.addLevel(document.querySelector('.c-drilldown__menulevel--trigger'), '0', []),
    model.addLevel(document.querySelector('.c-drilldown__menulevel--trigger'), '2', [
      model.buildLeaf('1', 'My first Leaf'),
      model.buildLeaf('2', 'My second Leaf'),
      model.buildLeaf('3', 'My third Leaf')
    ]),
    model.addLevel(document.querySelector('.c-drilldown__menulevel--trigger'), '2', [
      model.buildLeaf('1', 'My fourth Leaf'),
      model.buildLeaf('2', 'My fifth Leaf'),
      model.buildLeaf('3', 'My sixth Leaf')
    ]),
    model.filter({target: {value: 'SECoNd'}});
    assert.deepEqual(
      model.getFiltered(),
      [
        {
          id: '3',
          parent: '2',
          engaged: false,
          headerDisplayElement: document.querySelector('.c-drilldown__menulevel--trigger'),
          leaves: [
            { index: '1', text: 'My first Leaf', filtered: true },
            { index: '3', text: 'My third Leaf', filtered: true }
          ]
        },
        {
          id: '4',
          parent: '2',
          engaged: false,
          headerDisplayElement: document.querySelector('.c-drilldown__menulevel--trigger'),
          leaves: [
            { index: '1', text: 'My fourth Leaf', filtered: true },
            { index: '2', text: 'My fifth Leaf', filtered: true },
            { index: '3', text: 'My sixth Leaf', filtered: true }
          ]
        }
      ]
    );
  });
});