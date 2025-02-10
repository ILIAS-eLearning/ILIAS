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
import * as assert from 'node:assert';
import TreeSelectNode from '../../../../resources/js/Input/Field/src/TreeSelect/TreeSelectNode.js';
import createTreeSelectNodes
  from '../../../../resources/js/Input/Field/src/TreeSelect/createTreeSelectNodes.js';

describe('createTreeSelectNodes', function () {

  let basicNodeElementMock;

  beforeEach(function () {
    basicNodeElementMock = {
      nodeId: 'node-id',
      nodeName: 'node name',
      drilldownParentLevel: '0',
      contains: () => false,
      get classList() {
        return this;
      },
      get textContent() {
        return this.nodeName;
      },
      hasAttribute: () => false,
      getAttribute(attribute) {
        if (attribute === 'data-node-id') {
          return this.nodeId;
        }
        if (attribute === 'data-ddindex') {
          return this.drilldownParentLevel;
        }
        return null;
      },
      querySelector() {
        return this;
      },
      closest() {
        return this;
      },
    };
  });

  it('creates TreeSelectNode instances', function () {
    const nodeMap = createTreeSelectNodes([basicNodeElementMock], null);
    strict.equal(nodeMap.size, 1);
    strict.equal(nodeMap.has(basicNodeElementMock.nodeId), true);
    const node = nodeMap.get(basicNodeElementMock.nodeId);
    strict.equal(node instanceof TreeSelectNode, true);
    strict.equal(node.id, basicNodeElementMock.nodeId);
    strict.equal(node.name, basicNodeElementMock.nodeName);
    strict.equal(node.drilldownParentLevel, basicNodeElementMock.drilldownParentLevel);
  });

  it('merges new instances into an existing map', function () {
    const existingNodeElementMock = Object.create(basicNodeElementMock);
    existingNodeElementMock.nodeId = 'some-different-node-id';
    const existingMap = createTreeSelectNodes([existingNodeElementMock], null);

    const nodeMap = createTreeSelectNodes([basicNodeElementMock], existingMap);
    strict.equal(nodeMap.size, 2);
    strict.equal(nodeMap.has(existingNodeElementMock.nodeId), true);
    const node = nodeMap.get(existingNodeElementMock.nodeId);
    strict.equal(node instanceof TreeSelectNode, true);
    strict.equal(node.id, existingNodeElementMock.nodeId);
    strict.equal(node.name, existingNodeElementMock.nodeName);
    strict.equal(node.drilldownParentLevel, existingNodeElementMock.drilldownParentLevel);
  });

  it('throws an error for duplicates', function () {
    const existingNodeElementMock = Object.create(basicNodeElementMock);
    const existingMap = createTreeSelectNodes([existingNodeElementMock], null);

    assert.throws(() => {
      const nodeMap = createTreeSelectNodes([basicNodeElementMock], existingMap);
    }, Error);
  });

});
