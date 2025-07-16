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

import createTreeSelectNodes from './createTreeSelectNodes.js';
import querySelectorParents from '../../../../Core/src/querySelectorParents.js';
import walkArray from '../../../../Core/src/walkArray.js';
import sprintf from '../../../../Core/src/sprintf.js';
import * as CONSTANTS from './constants.js';

/**
 * @param {HTMLElement} element
 * @returns {string}
 * @throws {Error} if no data-node-id attribute exists.
 */
function getNodeIdOrAbort(element) {
  const nodeId = element.getAttribute(CONSTANTS.NODE_ID);
  if (nodeId === null) {
    throw new Error(`Could not find '${CONSTANTS.NODE_ID}' attribbute of element.`);
  }
  return nodeId;
}

/**
 * Returns a Map with all VALUES of larger which are not contained in smaller.
 *
 * @param {Map} larger
 * @param {Map} smaller
 * @returns {Array}
 */
function getMapDifference(larger, smaller) {
  return Array
    .from(larger.entries())
    .filter(([key]) => !smaller.has(key))
    .map(([, value]) => value);
}

/**
 * @param {HTMLLIElement} nodeElement
 * @param {boolean} selected
 */
function toggleSelectedNodeElementClass(nodeElement, selected) {
  nodeElement.classList.toggle(CONSTANTS.SELECTED_NODE_CLASS, selected);
}

/**
 * @param {HTMLElement} element
 * @returns {HTMLLIElement[]}
 */
function getNodeElements(element) {
  return Array.from(element.querySelectorAll(CONSTANTS.NODE));
}

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
export default class TreeSelect {
  /** @type {Map<string, TreeSelectNode>} (node-id => node) */
  #nodeMap;

  /** @type {Set<string>} (node-ids) */
  #nodeSelectionSet = new Set();

  /** @type {Set<string>} (async-node-ids) */
  #finishedRendering = new Set();

  /** @type {Set<string>} (async-node-ids) */
  #renderingQueue = new Set();

  /** @type {TemplateRenderer} */
  #templateRenderer;

  /** @type {AsyncRenderer} */
  #asyncRenderer;

  /** @type {{txt: function(string): string}} */
  #language;

  /** @type {Drilldown} */
  #drilldownComponent;

  /** @type {HTMLElement} */
  #breadcrumbsElement;

  /** @type {HTMLTemplateElement} */
  #breadcrumbTemplate;

  /** @type {HTMLUListElement} */
  #nodeSelectionElement;

  /** @type {HTMLTemplateElement} */
  #nodeSelectionTemplate;

  /** @type {HTMLButtonElement} */
  #dialogSelectButton;

  /** @type {HTMLButtonElement} */
  #dialogOpenButton;

  /** @type {HTMLDialogElement} */
  #dialogElement;

  /**
   * @param {Map<string, TreeSelectNode>} nodeMap (node-id => node)
   * @param {JQueryEventListener} jqueryEventListener
   * @param {TemplateRenderer} templateRenderer
   * @param {AsyncRenderer} asyncRenderer
   * @param {{txt: function(string): string}} language
   * @param {Drilldown} drilldownComponent
   * @param {HTMLElement} breadcrumbsElement
   * @param {HTMLTemplateElement} breadcrumbTemplate
   * @param {HTMLUListElement} nodeSelectionElement
   * @param {HTMLTemplateElement} nodeSelectionTemplate
   * @param {HTMLButtonElement} dialogSelectButton
   * @param {HTMLButtonElement} dialogOpenButton
   * @param {HTMLDialogElement} dialogElement
   */
  constructor(
    nodeMap,
    jqueryEventListener,
    templateRenderer,
    asyncRenderer,
    language,
    drilldownComponent,
    breadcrumbsElement,
    breadcrumbTemplate,
    nodeSelectionElement,
    nodeSelectionTemplate,
    dialogSelectButton,
    dialogOpenButton,
    dialogElement,
  ) {
    this.#nodeMap = nodeMap;
    this.#templateRenderer = templateRenderer;
    this.#asyncRenderer = asyncRenderer;
    this.#language = language;
    this.#drilldownComponent = drilldownComponent;
    this.#breadcrumbsElement = breadcrumbsElement;
    this.#breadcrumbTemplate = breadcrumbTemplate;
    this.#nodeSelectionElement = nodeSelectionElement;
    this.#nodeSelectionTemplate = nodeSelectionTemplate;
    this.#dialogSelectButton = dialogSelectButton;
    this.#dialogOpenButton = dialogOpenButton;
    this.#dialogElement = dialogElement;

    jqueryEventListener.on(
      this.#dialogElement.ownerDocument,
      this.#drilldownComponent.getBackSignal(),
      () => {
        this.#removeLastBreadcrumb();
      },
    );
    this.#dialogElement
      .querySelectorAll(CONSTANTS.CLOSE_ACTION)
      .forEach((button) => {
        button.addEventListener('click', () => {
          this.#closeDialog();
        });
      });
    this.#nodeSelectionElement
      .querySelectorAll('li')
      .forEach((entry) => {
        const nodeId = getNodeIdOrAbort(entry);
        this.#addRemoveNodeSelectionEntryClickHandler(entry, nodeId);
        this.#addNodeSelectionId(nodeId);
      });
    this.#drilldownComponent.addEngageListener((drilldownLevel) => {
      this.#engageDrilldownLevelHandler(drilldownLevel);
    });
    this.#dialogOpenButton.addEventListener('click', () => {
      this.#openDialog();
    });
    this.#nodeMap.forEach((node) => {
      this.#hydrateNode(node);
    });

    this.#updateDialogSelectButton();
  }

  /**
   * @param {string} nodeId
   */
  unselectNode(nodeId) {
    this.#removeNodeSelectionId(nodeId);
    this.#updateDialogSelectButton();
    this.#removeNodeSelectionEntry(nodeId);
    // check in case the node was not yet loaded (async).
    if (this.#nodeMap.has(nodeId)) {
      const node = this.#nodeMap.get(nodeId);
      toggleSelectedNodeElementClass(node.element, false);
      this.#changeNodeSelectButtonToSelect(node.selectButton, node.name);
      this.updateNodeSelectButtonStates();
    }
  }

  /**
   * @param {string} nodeId
   */
  selectNode(nodeId) {
    this.#addNodeSelectionId(nodeId);
    this.#updateDialogSelectButton();
    // check in case the node was not yet loaded (async).
    if (this.#nodeMap.has(nodeId)) {
      const node = this.#nodeMap.get(nodeId);
      toggleSelectedNodeElementClass(node.element, true);
      this.#changeNodeSelectButtonToUnselect(node.selectButton, node.name);
      this.#renderNodeSelectionEntry(node);
      this.updateNodeSelectButtonStates();
    }
  }

  /**
   * Updates the TreeSelectNode.selectButton state in the following manner:
   * - if no node is selected, enable all buttons
   * - if a node is selected, disable all other buttons
   */
  updateNodeSelectButtonStates() {
    this.#nodeMap.forEach((node, nodeId) => {
      if (this.#nodeSelectionSet.size > 0) {
        node.selectButton.disabled = !this.#nodeSelectionSet.has(nodeId);
        node.selectButton.querySelector(CONSTANTS.GLYPH).classList.toggle(
          CONSTANTS.DISABLED_CLASS,
          !this.#nodeSelectionSet.has(nodeId),
        );
      } else {
        node.selectButton.disabled = false;
        node.selectButton.querySelector(CONSTANTS.GLYPH).classList.toggle(
          CONSTANTS.DISABLED_CLASS,
          false,
        );
      }
    });
  }

  /**
   * @returns {Set<string>} (node-ids)
   */
  getSelection() {
    return new Set(this.#nodeSelectionSet);
  }

  /**
   * @returns {Map<string, TreeSelectNode>} (node-id => node)
   */
  getNodes() {
    return new Map(this.#nodeMap);
  }

  /**
   * Fetches child nodes from the given async node render URL and hydrates them.
   * This function will only fetch children once, and once at the same time.
   *
   * @param {string} asyncNodeId
   * @param {string} renderUrl
   * @param {HTMLUListElement} asyncNodeList
   * @returns {Promise<void>}
   */
  async #renderAsyncNodeChildren(asyncNode) {
    // only render the an async node once, and once at the same time.
    if (this.#finishedRendering.has(asyncNode.id) || this.#renderingQueue.has(asyncNode.id)) {
      return;
    }
    try {
      this.#renderingQueue.add(asyncNode.id);
      const childNodeElements = await this.#asyncRenderer.loadContent(asyncNode.renderUrl);
      asyncNode.listElement.append(...childNodeElements.children);
      this.#drilldownComponent.parseLevels();

      const updatedNodeMap = createTreeSelectNodes(
        getNodeElements(asyncNode.listElement),
        this.#nodeMap,
      );
      const addedNodes = getMapDifference(updatedNodeMap, this.#nodeMap);
      this.#nodeMap = updatedNodeMap;

      walkArray(addedNodes, (childNode) => {
        if (this.#nodeSelectionSet.has(childNode.id)) {
          this.selectNode(childNode.id);
        } else {
          this.unselectNode(childNode.id);
        }
        this.#hydrateNode(childNode);
      });
      this.#finishedRendering.add(asyncNode.id);
    } catch (error) {
      throw new Error(`Could not render async node children: ${error.message}`);
    } finally {
      this.#renderingQueue.delete(asyncNode.id);
    }
  }

  /**
   * @param {TreeSelectNode} node
   */
  #renderAllBreadcrumbs(node) {
    walkArray(querySelectorParents(node.element, CONSTANTS.NODE), (parentNodeElement) => {
      const parentNodeId = parentNodeElement.getAttribute(CONSTANTS.NODE_ID);
      if (parentNodeId === null || !this.#nodeMap.has(parentNodeId)) {
        throw new Error(`Could not find '${CONSTANTS.NODE_ID}' of node element.`);
      }
      const parentNode = this.#nodeMap.get(parentNodeId);
      this.#renderBreadcrumb(parentNode);
    });
  }

  /**
   * @param {string} drilldownLevel
   * @param {HTMLButtonElement} drilldownButton
   * @param {string} nodeName
   */
  #renderBreadcrumb(node) {
    const breadcrumb = this.#templateRenderer
      .createContent(this.#breadcrumbTemplate)
      .querySelector('.crumb');

    breadcrumb.setAttribute(CONSTANTS.DRILLDOWN_LEVEL, node.drilldownParentLevel);
    breadcrumb.firstElementChild.textContent = node.name;

    breadcrumb.addEventListener('click', () => {
      this.#drilldownComponent.engageLevel(node.drilldownParentLevel);
      node.drilldownButton.click();
    });

    this.#breadcrumbsElement.append(breadcrumb);
  }

  #removeLastBreadcrumb() {
    const breadcrumbs = this.#breadcrumbsElement.querySelectorAll('.crumb');
    breadcrumbs.item(breadcrumbs.length - 1)?.remove();
  }

  #removeAllBreadcrumbs() {
    walkArray(this.#breadcrumbsElement.querySelectorAll(CONSTANTS.CRUMB), (breadcrumb) => {
      breadcrumb.remove();
    });
  }

  /**
   * @param {string} drilldownLevel
   */
  #engageDrilldownLevelHandler(drilldownLevel) {
    // it should not be a string, this will definitely break here sometime.
    if (drilldownLevel === '0') {
      this.#removeAllBreadcrumbs();
      return;
    }
    const engagedNodeId = this.#dialogElement
      .querySelector(`ul[${CONSTANTS.DRILLDOWN_LEVEL}="${drilldownLevel}"]`)
      ?.closest(CONSTANTS.NODE)
      ?.getAttribute(CONSTANTS.NODE_ID);
    if (engagedNodeId === null || !this.#nodeMap.has(engagedNodeId)) {
      throw new Error(`Could not find node for drilldown-level '${drilldownLevel}'.`);
    }
    this.#removeAllBreadcrumbs();
    this.#renderAllBreadcrumbs(this.#nodeMap.get(engagedNodeId));
  }

  /**
   * @param {HTMLButtonElement} button
   * @param {TreeSelectNode} node
   */
  #addNodeDrilldownButtonClickHandler(button, node) {
    button.addEventListener('click', () => {
      if (node.renderUrl !== null) {
        this.#renderAsyncNodeChildren(node);
      }
    });
  }

  /**
   * @param {HTMLLIElement} nodeSelectionEntry
   * @param {string} nodeId
   */
  #addRemoveNodeSelectionEntryClickHandler(nodeSelectionEntry, nodeId) {
    nodeSelectionEntry
      .querySelector(CONSTANTS.REMOVE_ACTION)
      ?.addEventListener('click', () => {
        this.unselectNode(nodeId);
        nodeSelectionEntry.remove();
      });
  }

  /**
   * @param {HTMLButtonElement} button
   * @param {TreeSelectNode} node
   */
  #addNodeSelectButtonClickHandler(button, node) {
    button.addEventListener('click', () => {
      if (this.#nodeSelectionSet.has(node.id)) {
        this.unselectNode(node.id);
      } else {
        this.selectNode(node.id);
      }
    });
  }

  /**
   * @param {TreeSelectNode} node
   */
  #renderNodeSelectionEntry(node) {
    if (this.#nodeSelectionElement.querySelector(`li[${CONSTANTS.NODE_ID}="${node.id}"]`) !== null) {
      return;
    }
    const nodeSelectionEntry = this.#templateRenderer.createContent(this.#nodeSelectionTemplate);
    const listElement = nodeSelectionEntry.querySelector('[data-node-id]');

    listElement.setAttribute(CONSTANTS.NODE_ID, node.id);
    listElement.querySelector(`[${CONSTANTS.NODE_NAME}]`).textContent = node.name;
    listElement.querySelector('input').value = node.id;

    this.#addRemoveNodeSelectionEntryClickHandler(listElement, node.id);

    this.#nodeSelectionElement.append(...nodeSelectionEntry.children);
  }

  /**
   * @param {string} nodeId
   */
  #removeNodeSelectionEntry(nodeId) {
    this.#nodeSelectionElement.querySelector(`li[${CONSTANTS.NODE_ID}="${nodeId}"]`)?.remove();
  }

  /**
   * @param {TreeSelectNode} node
   */
  #hydrateNode(node) {
    this.#addNodeSelectButtonClickHandler(node.selectButton, node);
    if (node.drilldownButton !== null) {
      this.#addNodeDrilldownButtonClickHandler(node.drilldownButton, node);
    }
  }

  /**
   * @param {HTMLButtonElement} button
   * @param {string} nodeName
   */
  #changeNodeSelectButtonToSelect(button, nodeName) {
    button.querySelector(CONSTANTS.REMOVE_ACTION)?.classList.add(CONSTANTS.HIDDEN_CLASS);
    button.querySelector(CONSTANTS.SELECT_ACTION)?.classList.remove(CONSTANTS.HIDDEN_CLASS);
    button.setAttribute('aria-label', this.#translate('select_node', nodeName));
  }

  /**
   * @param {HTMLButtonElement} button
   * @param {string} nodeName
   */
  #changeNodeSelectButtonToUnselect(button, nodeName) {
    button.querySelector(CONSTANTS.SELECT_ACTION)?.classList.add(CONSTANTS.HIDDEN_CLASS);
    button.querySelector(CONSTANTS.REMOVE_ACTION)?.classList.remove(CONSTANTS.HIDDEN_CLASS);
    button.setAttribute('aria-label', this.#translate('unselect_node', nodeName));
  }

  #updateDialogSelectButton() {
    this.#dialogSelectButton.disabled = (this.#nodeSelectionSet.size <= 0);
  }

  /**
   * @param {string} node
   */
  #removeNodeSelectionId(nodeId) {
    if (this.#nodeSelectionSet.has(nodeId)) {
      this.#nodeSelectionSet.delete(nodeId);
    }
  }

  /**
   * @param {string} nodeId
   */
  #addNodeSelectionId(nodeId) {
    if (!this.#nodeSelectionSet.has(nodeId)) {
      this.#nodeSelectionSet.add(nodeId);
    }
  }

  /**
   * @param {string} variable
   * @param {...any} substitutes
   * @returns {string}
   */
  #translate(variable, ...substitutes) {
    return sprintf(this.#language.txt(variable), substitutes);
  }

  #closeDialog() {
    this.#dialogElement.close();
  }

  #openDialog() {
    this.#dialogElement.showModal();
  }
}
