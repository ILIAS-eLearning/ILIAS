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

import FormNode from './formnode.class.js';

/**
 * The attribute marks a DOM node as UIForm node and gives the type of
 * Component yielding this node.
 * @type {string}
 */
const FORMNODE_ATTRIBUTE = 'data-il-ui-type';
const SEARCH_FORMNODE = '[data-il-ui-type]';

/**
 * @type {string}
 */
const SEARCH_FIELD = '[name]';

/**
 * Nodes of these types will not show up as level in the value's representation
 * @type {Array<string>}
 */
const TRANSPARENT_NODES = ['DependantFields'];

export default class Container {
/**
 * A collection of final processing functions
 * to present values for specific Input types.
 * Functions are called with FormNode as single parameter.
 * @name valueRepresentation
 * @function
 * @param {FormNode} node
 * @return {Array<string>}
 *
 *
 * @type {Object<string,class>}
 */
  #transforms;

  /**
   * @type {HTMLElement}
   */
  #container;

  /**
   * @type {FormNode}
   */
  #nodes;

  /**
   * @param {Object<string,Class>} transforms
   * @param {HTMLElement} container
   * @return {void}
   */
  constructor(transforms, container) {
    this.#transforms = transforms;
    this.#container = container;
    this.#nodes = new FormNode('form', 'FormContainerInput', container.getAttribute('id'));

    const ilTopInputDomElements = Array.from(
      container.querySelectorAll(SEARCH_FORMNODE),
    ).filter((element) => !element.parentNode.closest(SEARCH_FORMNODE));

    ilTopInputDomElements.forEach(
      (topInputDomElement) => this.#register(topInputDomElement, this.#nodes),
    );
  }

  /**
   * @param {HTMLElement} outerDomNode
   * @param {FormNode} node
   * @return {void}
   */
  #register(outerDomNode, node) {
    const label = this.#getLabel(outerDomNode);
    const type = outerDomNode.getAttribute(FORMNODE_ATTRIBUTE);
    const nuNode = new FormNode(
      label,
      type,
      outerDomNode.getAttribute('id'),
    );
    nuNode.setTransforms(this.#getTransformsFor(type));

    const inputFields = this.#getInputFields(outerDomNode);
    inputFields.forEach(
      (field) => nuNode.addHtmlField(field),
    );

    const ilUIFormNodes = this.#getIlUIFormNodes(outerDomNode);
    ilUIFormNodes.forEach(
      (domNode) => this.#register(domNode, nuNode),
    );
    node.addChildNode(nuNode);
  }

  /**
   * @param {HTMLElement} outerDomNode
   * @return {HTMLElement[]}
   */
  #getIlUIFormNodes(outerDomNode) {
    return Array.from(
      outerDomNode.querySelectorAll(SEARCH_FORMNODE),
    ).filter((element) => element.parentNode.closest(SEARCH_FORMNODE) === outerDomNode);
  }

  /**
   * @param {HTMLElement} outerDomNode
   * @return {HTMLElement[]}
   */
  #getInputFields(outerDomNode) {
    return Array.from(
      outerDomNode.querySelectorAll(SEARCH_FIELD),
    ).filter((element) => element.parentNode.closest(SEARCH_FORMNODE) === outerDomNode);
  }

  /**
   * @param {HTMLElement} outerDomNode
   * @return {string}
   */
  #getLabel(outerDomNode) {
    let label = '';
    const labelNode = Array.from(
      outerDomNode.querySelectorAll('label'),
    ).filter((element) => element.parentNode.closest(SEARCH_FORMNODE) === outerDomNode);
    if (labelNode.length > 0) {
      label = labelNode[0].textContent;
    }
    return label;
  }

  /**
   * @return {FormNode}
   */
  getNodes() {
    return this.#nodes;
  }

  /**
   * @param {?FormNode} initNode
   * @param {?FormNode[]} initOut
   * @return {FormNode[]}
   */
  getNodesFlat(initNode, initOut) {
    let out = initOut;
    let node = initNode;
    if (!out) {
      out = [];
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
   * @param {string} id
   * @param {?FormNode} initEntry
   * @return {FormNode}
   */
  getNodeById(id, initEntry) {
    let entry = initEntry;
    if (!entry) {
      entry = this.#nodes;
    }

    if (entry.getId() === id) {
      return entry;
    }

    let ret = null;
    entry.getChildren().forEach(
      (child) => {
        ret = this.getNodeById(id, child);
      },
    );
    return ret;
  }

  /**
   * @param {?FormNode} initNode
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

    if (TRANSPARENT_NODES.includes(node.getType())) {
      indent -= 1;
    } else {
      const entry = {
        label: node.getLabel(),
        value: node.getValuesRepresentation(),
        indent,
        type: node.getType(),
      };
      out.push(entry);
    }

    node.getChildren().forEach(
      (child) => this.getValuesRepresentation(child, indent + 1, out),
    );
    return out;
  }

  /**
   * @param {string} type
   * @return {valueRepresentation}
   */
  #getTransformsFor(type) {
    if (this.#transforms[type]) {
      console.log(`transforms found for ${type}`);
      return this.#transforms[type];
    }
    return null;
  }
}
