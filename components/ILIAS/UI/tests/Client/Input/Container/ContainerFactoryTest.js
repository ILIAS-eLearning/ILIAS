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
import ContainerFactory from '../../../../resources/js/Input/Container/src/ContainerFactory.js';
import Container from '../../../../resources/js/Input/Container/src/Container.js';

describe('ContainerFactory', () => {
  it('creates Container instances.', () => {
    const formElement = {
      querySelectorAll: () => [],
      classList: { contains: () => false },
    };
    const document = {
      getElementById: mock.fn(() => formElement),
    };
    const containerFactory = new ContainerFactory(document);
    const formId = 'some-form-id';
    const containerInstance = containerFactory.createContainer(formId);
    strict.equal(document.getElementById.mock.callCount(), 1);
    strict.equal(document.getElementById.mock.calls[0].arguments[0], formId);
    strict.ok(containerInstance instanceof Container);
  });

  it('stores Container instances.', () => {
    const formElement = {
      querySelectorAll: () => [],
      classList: { contains: () => false },
    };
    const document = {
      getElementById: () => formElement,
    };
    const containerFactory = new ContainerFactory(document);
    const formId = 'some-form-id';
    const initialContainerInstance = containerFactory.createContainer(formId);
    const storedContainerInstance = containerFactory.get(formId);
    strict.deepEqual(initialContainerInstance, storedContainerInstance);
  });
});
