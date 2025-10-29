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

import { beforeEach, describe, it } from 'node:test';
import { strict } from 'node:assert/strict';
import OptionFilter from '../../../../resources/js/Input/Field/src/OptionFilter/OptionFilter.js';
import OptionFilterFactory
  from '../../../../resources/js/Input/Field/src/OptionFilter/OptionFilterFactory.js';

describe('OptionFilterFactory', () => {
  let elementMock;
  const someId = 'someId';

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
      addEventListener: () => {
      },
      getAttribute: () => 'radio-field-input',
      id: someId,
    };
  });

  it('init returns component', () => {
    const factory = new OptionFilterFactory();
    const component = factory.init(elementMock);
    strict.equal((component instanceof OptionFilter), true);
  });

  it('init creates & registers instances of InputHasOptionFilter', () => {
    const factory = new OptionFilterFactory();
    factory.init(elementMock);
    strict.equal((factory.get(someId) instanceof OptionFilter), true);
  });

  it('get with valid id returns instance', () => {
    const factory = new OptionFilterFactory();
    const componentFromInit = factory.init(elementMock);
    const componentFromRegistry = factory.get(someId);
    strict.deepEqual(componentFromInit, componentFromRegistry);
  });

  it('init throws for undefined elements', () => {
    const factory = new OptionFilterFactory();
    strict.throws(
      () => factory.init(undefined),
      {
        name: 'TypeError',
        message: 'During init of an InputHasOptionFilter an undefined element was passed to the factory.',
      },
    );
  });

  it('init throws for element with same id already initialized', () => {
    const factory = new OptionFilterFactory();
    factory.init(elementMock);
    strict.throws(
      () => factory.init(elementMock),
      {
        name: 'Error',
      },
    );
  });
});
