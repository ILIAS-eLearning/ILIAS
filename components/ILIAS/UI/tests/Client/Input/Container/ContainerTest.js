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
import Container from '../../../../resources/js/Input/Container/src/Container.js';

describe('Container', () => {
  it('returns a copy of its Field instances.', () => {
    const fields = [{ name: 'test' }];
    const container = new Container(fields);
    const shallowCopy = container.getFields();
    shallowCopy.pop();
    strict.notEqual(fields, shallowCopy);
    strict.equal(fields.length, 1);
    strict.notEqual(shallowCopy.length, 1);
  });

  it('can flatten its Field instances.', () => {
    const field1 = {
      name: 'field 1',
      getChildren: () => [],
    };
    const field2 = {
      name: 'field 2',
      getChildren: () => [field1],
    };
    const container = new Container([field2]);
    const flatFields = container.getFieldsFlat();
    strict.equal(flatFields.length, 2);
    strict.deepEqual(flatFields, [field2, field1]);
    strict.notDeepEqual(flatFields, [field2]);
  });

  it('applies reducers to its Field instances.', () => {
    const fieldType = 'test';
    const field = {
      type: fieldType,
      reduceWith: mock.fn((reducer) => reducer(field, [])),
    };
    const container = new Container([field]);
    const matchingReducer = mock.fn(() => null);
    const notMatchingReducer = mock.fn(() => null);
    container.addFieldReducer(fieldType, matchingReducer);
    container.addFieldReducer('other-test-type', notMatchingReducer);
    container.reduceFields();
    strict.equal(matchingReducer.mock.callCount(), 1);
    strict.equal(notMatchingReducer.mock.callCount(), 0);
    strict.equal(field.reduceWith.mock.callCount(), 1);
    strict.deepEqual(matchingReducer.mock.calls[0].arguments[0], field);
    strict.deepEqual(matchingReducer.mock.calls[0].arguments[1], []);
  });

  it('finds Field instances by name.', () => {
    const fieldName = 'test/input_1';
    const field1 = {
      name: fieldName,
      getChildren: () => [],
    };
    const field2 = {
      name: 'test/input_2',
      getChildren: () => [field1],
    };
    const container = new Container([field2]);
    const foundField = container.getFieldByName(fieldName);
    strict.deepEqual(foundField, field1);
  });
});
