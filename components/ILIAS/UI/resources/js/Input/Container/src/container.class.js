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
 */

import Presentation from './presentation.class.js';

/**
 * The attribute marks a DOM node as UIForm node and gives the type of the
 * Component yielding this node.
 * @type {string}
 */
const FIELD_ATTRIBUTE_TYPE = 'data-il-ui-component';

/**
 * The attribute marks a DOM node as UIForm node and gives the name of the
 * Node as provided by the Namesource
 * @type {string}
 */
const FIELD_ATTRIBUTE_NAME = 'data-il-ui-input-name';

/**
 * @type {string}
 */
const SEARCH_FIELD = `fieldset[${FIELD_ATTRIBUTE_TYPE}][ ${FIELD_ATTRIBUTE_NAME}]`;

/**
 * @type {string}
 */
const FIELD_INPUT_AREA = '.c-input__field';

/**
 * @type {string}
 */
const FIELD_LABEL = '.c-input__label';

/**
 * @type {string}
 */
const SEARCH_INPUT = '[name]';

export default class Container {
/**
 * A collection of final processing functions
 * to present values for specific Input types.
 * Functions are called with Presentation as single parameter.
 * @name valueRepresentation
 * @function
 * @param {Presentation} node
 * @return {Array<string>}
 *
 *
 * @type {Object<string,function>}
 */
  #hooks;

  /**
   * @type {HTMLElement}
   */
  #container;

  /**
   * @type {Presentation}
   */
  #nodes;

  /**
   * @param {Object<string,function>} hooks
   * @param {HTMLElement} container
   * @return {void}
   */
  constructor(hooks, container) {
    this.#hooks = hooks;
    this.#container = container;
    this.#nodes = new Presentation('form', 'FormContainerInput', container.getAttribute('id'));

    Array.from(container.querySelectorAll(SEARCH_FIELD))
      .filter((domFieldNode) => domFieldNode.parentNode === domFieldNode.closest('form'))
      .forEach((domFieldNode) => this.#discoverHTMLElementsRecursively(this.#nodes, domFieldNode));
  }

  /**
   * @return {Presentation}
   */
  getNodes() {
    return this.#nodes;
  }

  /**
   * @param {string} name
   * @param {?Presentation} initEntry
   * @return {Presentation}
   */
  getNodeByName(name, initEntry) {
    let entry = initEntry;
    if (!entry) {
      entry = this.#nodes;
    }

    const parts = name.split('/').slice(1);
    parts.forEach(
      (part) => {
        entry = entry.getChildByName(part);
        if (!entry) {
          return null;
        }
        return entry;
      },
    );
    return entry;
  }

  /**
   * @param {?Presentation} initNode
   * @param {?number} initIndent
   * @param {?Array} initOut
   * @return {Array}
   */
  getValuesRepresentation(initNode, initIndent, initOut) {
    let node = initNode;
    if (!node) {
      node = this.getNodes();
    }

    let indent = initIndent;
    if (!indent) {
      indent = 0;
    }
    let out = initOut;
    if (!out) {
      out = [];
    }

    const entry = {
      label: node.getLabel(),
      value: node.getValuesRepresentation(),
      indent,
      type: node.getType(),
    };
    out.push(entry);

    node.getChildren().forEach(
      (child) => this.getValuesRepresentation(child, indent + 1, out),
    );
    return out;
  }

  /**
   * @param {?Presentation} initNode
   * @param {?Presentation[]} initOut
   * @return {Presentation[]}
   */
  getNodesFlat(initNode, initOut) {
    let out = initOut;
    let node = initNode;
    if (!out) {
      out = [];
    }
    if (!node) {
      node = this.#nodes;
    }

    out.push(node);
    const children = node.getChildren();
    if (children.length > 0) {
      children.forEach(
        (child) => this.getNodesFlat(child, out),
      );
    }
    return out;
  }

  /**
   * @param {Presentation} current
   * @param {HTMLElement} domFieldNode
   * @return {void}
   */
  #discoverHTMLElementsRecursively(current, domFieldNode) {
    const node = this.#buildNode(domFieldNode);
    current.addChildNode(node);

    const furtherChildren = Array.from(domFieldNode.querySelectorAll(`${FIELD_INPUT_AREA} ${SEARCH_FIELD}`))
      .filter(
        (cn) => cn.closest(FIELD_INPUT_AREA) === domFieldNode.querySelector(FIELD_INPUT_AREA),
      );
    if (furtherChildren.length > 0) {
      furtherChildren.forEach((domChildFieldNode) => this.#discoverHTMLElementsRecursively(
        node,
        domChildFieldNode,
      ));
    }
  }

  #buildNode(domFieldNode) {
    const type = domFieldNode.getAttribute(FIELD_ATTRIBUTE_TYPE);
    const name = domFieldNode.getAttribute(FIELD_ATTRIBUTE_NAME);
    const label = domFieldNode.querySelector(FIELD_LABEL).textContent.trim();

    const node = new Presentation(type, name, label);
    node.setHooks(this.#getHooksFor(type));

    Array.from(domFieldNode.querySelectorAll(SEARCH_INPUT))
      .filter(
        (input) => input.closest(SEARCH_FIELD) === domFieldNode,
      )
      .forEach((input) => node.addHtmlField(input));

    return node;
  }

  /**
   * @param {string} type
   * @return {valueRepresentation}
   */
  #getHooksFor(type) {
    if (this.#hooks[type]) {
      return this.#hooks[type];
    }
    return null;
  }
}
