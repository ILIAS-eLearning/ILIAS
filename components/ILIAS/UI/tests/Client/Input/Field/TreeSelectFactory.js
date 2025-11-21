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

import { beforeEach, describe, it } from 'node:test';
import { strict } from 'node:assert/strict';
import TreeSelect from '../../../../resources/js/Input/Field/src/TreeSelect/TreeSelect.js';
import TreeSelectFactory
  from '../../../../resources/js/Input/Field/src/TreeSelect/TreeSelectFactory.js';

describe('TreeSelectFactory', () => {
  let elementMock;
  let documentMock;
  let languageMock;
  let drilldownMock;
  let drilldownFactoryMock;
  let jQueryEventListenerMock;

  beforeEach(() => {
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
      addEventListener: () => {},
    };
    documentMock = {
      getElementById: () => elementMock,
    };
    languageMock = {
      txt: (s) => s,
    };
    drilldownMock = {
      getBackSignal: () => '',
      addEngageListener: () => {},
      getCurrentLevel: () => '0',
      getParentLevel: () => null,
    };
    drilldownFactoryMock = {
      getInstance: () => drilldownMock,
    };
    jQueryEventListenerMock = {
      on: () => {},
    };
  });

  it('returns TreeSelect instances', () => {
    const factory = new TreeSelectFactory(
      jQueryEventListenerMock,
      drilldownFactoryMock,
      languageMock,
      documentMock,
    );

    const component = factory.initTreeSelect('some-id');
    strict.equal((component instanceof TreeSelect), true);
  });

  it('returns TreeMultiSelect instances', () => {
    const factory = new TreeSelectFactory(
      jQueryEventListenerMock,
      drilldownFactoryMock,
      languageMock,
      documentMock,
    );

    const component = factory.initTreeMultiSelect('some-id', false);
    strict.equal((component instanceof TreeSelect), true);
  });

  it('stores TreeSelect instances', () => {
    const factory = new TreeSelectFactory(
      jQueryEventListenerMock,
      drilldownFactoryMock,
      languageMock,
      documentMock,
    );

    const htmlId = 'some-id';
    const component1 = factory.initTreeSelect(htmlId);
    const component2 = factory.getInstance(htmlId);
    strict.equal((component1 instanceof TreeSelect), true);
    strict.equal((component2 instanceof TreeSelect), true);
    strict.deepEqual(component2, component1);
  });

  it('stores TreeMultiSelect instances', () => {
    const factory = new TreeSelectFactory(
      jQueryEventListenerMock,
      drilldownFactoryMock,
      languageMock,
      documentMock,
    );

    const htmlId = 'some-id';
    const component1 = factory.initTreeMultiSelect(htmlId, false);
    const component2 = factory.getInstance(htmlId);
    strict.equal((component1 instanceof TreeSelect), true);
    strict.equal((component2 instanceof TreeSelect), true);
    strict.deepEqual(component2, component1);
  });
});
