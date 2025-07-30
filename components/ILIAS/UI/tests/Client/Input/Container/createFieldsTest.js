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

import { describe, it, mock } from 'node:test';
import { strict } from 'node:assert/strict';
import createFields from '../../../../resources/js/Input/Container/src/Field/createFields.js';

describe('createFields', () => {
  it('finds fields in HTMLFormElement', () => {
    const formElement = {
      querySelectorAll: mock.fn(() => []),
      classList: { contains: mock.fn(() => false) },
    };
    createFields(formElement);
    strict.equal(formElement.classList.contains.mock.callCount(), 1);
    strict.equal(formElement.querySelectorAll.mock.callCount(), 1);
    // regular expression matching `:scope > SOMETHING`
    const pattern = /^:scope\s*>\s*[^>]+$/;
    strict.match(formElement.querySelectorAll.mock.calls[0].arguments[0], pattern);
    mock.reset();
  });

  it('finds subsequent fields in HTMLFieldSetElement', () => {
    const fieldElement = {
      querySelectorAll: mock.fn(() => []),
      classList: { contains: mock.fn(() => true) },
    };
    createFields(fieldElement);
    strict.equal(fieldElement.classList.contains.mock.callCount(), 1);
    strict.equal(fieldElement.querySelectorAll.mock.callCount(), 1);
    // regular expression matching `:scope > SOMETHING > SOMETHING`
    const pattern = /^:scope\s*>\s*[^>]+\s*>\s*[^>]+$/;
    strict.match(fieldElement.querySelectorAll.mock.calls[0].arguments[0], pattern);
    mock.reset();
  });

  it('finds associated input element(s) of field', () => {
    const fieldName = 'test/input_1/input_2';
    const validInputElements = [
      { name: fieldName },
      { name: `${fieldName}[]` },
    ];
    const invalidInputElements = [
      { name: `${fieldName}/input_X` },
      { name: `${fieldName}/input_X[]` },
    ];
    const fieldElement = {
      name: fieldName,
      hasAttribute: () => true,
      getAttribute: () => fieldName,
      querySelector: () => null,
      querySelectorAll: () => [],
      classList: { contains: () => false },
      parentElement: {
        querySelectorAll: mock.fn(() => [...validInputElements, ...invalidInputElements]),
      },
    };
    const formElement = {
      querySelectorAll: () => [fieldElement],
      classList: { contains: () => false },
    };
    const fields = createFields(formElement);
    strict.equal(fieldElement.parentElement.querySelectorAll.mock.callCount(), 1);
    strict.equal(fieldElement.parentElement.querySelectorAll.mock.calls[0].arguments[0], `[name^="${fieldName}"]`);
    strict.equal(fields.length, 1);
    strict.deepEqual(fields[0].getInputs(), validInputElements);
    mock.reset();
  });

  it('creates field composite tree recursively', () => {
    const fieldElement = {
      getAttributeReturnValues: ['type', 'name'],
      getAttributeCallCount: 0,
      querySelector: () => null,
      hasAttribute: () => true,
      getAttribute() {
        const value = this.getAttributeReturnValues[this.getAttributeCallCount];
        this.getAttributeCallCount += 1;
        return value;
      },
      parentElement: {
        querySelectorAll: () => [],
      },
      classList: { contains: () => true },
    };
    const fieldElement1 = {
      __proto__: fieldElement,
      querySelectorAll: () => [],
    };
    const fieldElement2 = {
      __proto__: fieldElement,
      querySelectorAll: () => [fieldElement1],
    };
    const fieldElement3 = {
      __proto__: fieldElement,
      querySelectorAll: () => [fieldElement2],
    };
    const formElement = {
      querySelectorAll: () => [fieldElement3],
      classList: { contains: () => false },
    };
    const fields = createFields(formElement);
    strict.equal(fields.length, 1);
    strict.equal(fields[0].getChildren().length, 1);
    strict.equal(fields[0].getChildren()[0].getChildren().length, 1);
    strict.equal(fields[0].getChildren()[0].getChildren()[0].getChildren().length, 0);
  });
});
