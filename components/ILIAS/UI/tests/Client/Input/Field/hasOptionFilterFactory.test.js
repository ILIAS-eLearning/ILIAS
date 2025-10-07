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
import InputHasOptionFilterContextFactory
  from '../../../../resources/js/Input/Field/src/hasQuickFilterContext/hasOptionFilter.factory.js';
import InputHasOptionFilterContext
  from '../../../../resources/js/Input/Field/src/hasQuickFilterContext/hasOptionFilter.class.js';

describe('InputHasOptionFilterContextFactory', () => {
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
    const factory = new InputHasOptionFilterContextFactory();
    const component = factory.init(elementMock);
    strict.equal((component instanceof InputHasOptionFilterContext), true);
  });

  it('init creates & registers instances of InputHasOptionFilter', () => {
    const factory = new InputHasOptionFilterContextFactory();
    factory.init(elementMock);
    strict.equal((factory.instances[someId] instanceof InputHasOptionFilterContext), true);
  });

  it('get with valid id returns instance', () => {
    const factory = new InputHasOptionFilterContextFactory();
    const componentFromInit = factory.init(elementMock);
    const componentFromRegistry = factory.get(someId);
    strict.deepEqual(componentFromInit, componentFromRegistry);
  });

  it('init throws for undefined elements', () => {
    const factory = new InputHasOptionFilterContextFactory();
    strict.throws(
      () => factory.init(undefined),
      {
        name: 'TypeError',
        message: 'During init of an InputHasOptionFilter an undefined element was passed to the factory.',
      },
    );
  });

  it('init throws for element with same id already initialized', () => {
    const factory = new InputHasOptionFilterContextFactory();
    factory.init(elementMock);
    strict.throws(
      () => factory.init(elementMock),
      {
        name: 'Error',
      },
    );
  });
});
