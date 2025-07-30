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
import Field from '../../../../resources/js/Input/Container/src/Field/Field.js';

describe('Field', () => {
  it('returns a copy of its input elements.', () => {
    const input = { name: 'test/input_1' };
    const inputElements = [input];
    const field = new Field('', '', '', inputElements, {});
    const shallowCopy = field.getInputs();
    shallowCopy.pop();
    strict.notDeepEqual(inputElements, shallowCopy);
    strict.equal(inputElements.length, 1);
    strict.notEqual(shallowCopy.length, 1);
  });

  it('returns a copy of its (Field) children.', () => {
    const child = { type: 'test' };
    const fieldChildren = [child];
    const field = new Field('', '', '', [], {}, fieldChildren);
    const shallowCopy = field.getChildren();
    shallowCopy.pop();
    strict.notDeepEqual(fieldChildren, shallowCopy);
    strict.equal(fieldChildren.length, 1);
    strict.notEqual(shallowCopy.length, 1);
  });

  it('keeps its input elements private.', () => {
    const input = { name: 'test/input_1' };
    const inputElements = [input];
    const field = new Field('', '', '', inputElements, {});
    strict.equal(field.inputs, undefined);
  });

  it('keeps its (Field) children private.', () => {
    const child = { type: 'test' };
    const fieldChildren = [child];
    const field = new Field('', '', '', [], {}, fieldChildren);
    strict.equal(field.children, undefined);
  });

  it('can be reduceed recursively.', () => {
    const field1 = new Field('', 'field 1', null, [], {}, []);
    const field2 = new Field('', 'field 2', null, [], {}, [field1]);
    const field3 = new Field('', 'field 3', null, [], {}, [field2]);
    const reducer = mock.fn((field, children) => [field, children]);
    const field1Result = [field1, []];
    const field2Result = [field2, [field1Result]];
    const field3Result = [field3, [field2Result]];
    field3.reduceWith(reducer);
    strict.equal(reducer.mock.callCount(), 3);
    strict.deepEqual(reducer.mock.calls[0].arguments[0], field1);
    strict.deepEqual(reducer.mock.calls[0].arguments[1], []);
    strict.deepEqual(reducer.mock.calls[0].result, field1Result);
    strict.deepEqual(reducer.mock.calls[1].arguments[0], field2);
    strict.deepEqual(reducer.mock.calls[1].arguments[1], [field1Result]);
    strict.deepEqual(reducer.mock.calls[1].result, field2Result);
    strict.deepEqual(reducer.mock.calls[2].arguments[0], field3);
    strict.deepEqual(reducer.mock.calls[2].arguments[1], [field2Result]);
    strict.deepEqual(reducer.mock.calls[2].result, field3Result);
    mock.reset();
  });
});
