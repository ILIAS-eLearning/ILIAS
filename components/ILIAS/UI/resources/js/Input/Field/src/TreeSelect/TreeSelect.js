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
 * @param {HTMLUListElement}
 * @param {string} nodeId
 */
function removeNodeListEntry(listElement, nodeId) {
  listElement.querySelector(`li[${CONSTANTS.NODE_ID}="${nodeId}"]`)?.remove();
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

  /**
   * The node selection element contains node entries inside the surrounding form.
   * @type {HTMLUListElement}
   */
  #nodeSelectionElement;

  /** @type {HTMLTemplateElement} */
  #nodeSelectionEntryTemplate;

  /** @type {HTMLButtonElement} */
  #dialogSelectButton;

  /** @type {HTMLButtonElement} */
  #dialogOpenButton;

  /** @type {HTMLDialogElement} */
  #dialogElement;

  /** @type {function(TreeSelect): void} */
  #selectionHandler;

  /**
   * The node dropdown element MAY contain node entries inside the dialog.
   * @type {HTMLUListElement|null}
   */
  #nodeDropdownMenuElement;

  /** @type {counterObject|null} */
  #nodeDropdownCounter;

  /** @type {HTMLTemplateElement|null} */
  #nodeDropdownMenuEntryTemplate;

  /** @type {HTMLLIElement|null} */
  #nodeDropdownMenuEntryPlaceholder;

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
   * @param {HTMLTemplateElement} nodeSelectionEntryTemplate
   * @param {HTMLButtonElement} dialogSelectButton
   * @param {HTMLButtonElement} dialogOpenButton
   * @param {HTMLDialogElement} dialogElement
   * @param {function(TreeSelect): void} selectionHandler
   * @param {HTMLUListElement|null} nodeDropdownElement
   * @param {counterObject|null} nodeDropdownCounter
   * @param {HTMLTemplateElement|null} nodeDropdownEntryTemplate
   * @param {HTMLLIElement|null} nodeDropdownEntryPlaceholder
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
    nodeSelectionEntryTemplate,
    dialogSelectButton,
    dialogOpenButton,
    dialogElement,
    selectionHandler,
    nodeDropdownMenuElement = null,
    nodeDropdownCounter = null,
    nodeDropdownMenuEntryTemplate = null,
    nodeDropdownMenuEntryPlaceholder = null,
  ) {
    this.#nodeMap = nodeMap;
    this.#templateRenderer = templateRenderer;
    this.#asyncRenderer = asyncRenderer;
    this.#language = language;
    this.#drilldownComponent = drilldownComponent;
    this.#breadcrumbsElement = breadcrumbsElement;
    this.#breadcrumbTemplate = breadcrumbTemplate;
    this.#nodeSelectionElement = nodeSelectionElement;
    this.#nodeSelectionEntryTemplate = nodeSelectionEntryTemplate;
    this.#dialogSelectButton = dialogSelectButton;
    this.#dialogOpenButton = dialogOpenButton;
    this.#dialogElement = dialogElement;
    this.#selectionHandler = selectionHandler;
    this.#nodeDropdownMenuElement = nodeDropdownMenuElement;
    this.#nodeDropdownCounter = nodeDropdownCounter;
    this.#nodeDropdownMenuEntryTemplate = nodeDropdownMenuEntryTemplate;
    this.#nodeDropdownMenuEntryPlaceholder = nodeDropdownMenuEntryPlaceholder;

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
    this.#drilldownComponent.addEngageListener((drilldownLevel) => {
      this.#engageDrilldownLevelHandler(drilldownLevel);
    });
    this.#dialogOpenButton.addEventListener('click', () => {
      this.#openDialog();
    });
    this.#nodeMap.forEach((node) => {
      this.#hydrateNode(node);
    });
    this.#nodeSelectionElement
      .querySelectorAll('li')
      .forEach((entry) => {
        const nodeId = getNodeIdOrAbort(entry);
        this.#addRemoveNodeListEntryClickHandler(entry, nodeId);
        this.selectNode(nodeId);
      });
    this.#nodeDropdownMenuElement
      ?.querySelectorAll(CONSTANTS.TREE_SELECT_DROPDOWN_ENTRIES)
      ?.forEach((entry) => {
        const nodeId = getNodeIdOrAbort(entry);
        this.#addRemoveNodeListEntryClickHandler(entry, nodeId);
        this.#addEngageNodeDropdownEntryClickHandler(entry, nodeId);
      });

    // trigger drilldown engage listener for already engaged level once.
    this.#engageDrilldownLevelHandler(this.#drilldownComponent.getCurrentLevel());
  }

  /**
   * @param {string} nodeId
   */
  unselectNode(nodeId) {
    this.#removeNodeSelectionId(nodeId);
    this.#updateDialogSelectButton();
    this.#removeNodeSelectionListEntry(nodeId);
    this.#removeNodeDropdownMenuEntry(nodeId);
    this.#updateNodeDropdownCounter();
    this.#updateNodeDropdownMenuEntryPlaceholder();
    // check in case the node does not exist anymore (Input::withValue()).
    if (this.#nodeMap.has(nodeId)) {
      const node = this.#nodeMap.get(nodeId);
      toggleSelectedNodeElementClass(node.element, false);
      this.#changeNodeSelectButtonToSelect(node.selectButton, node.name);
    }
    this.#selectionHandler(this);
  }

  /**
   * @param {string} nodeId
   */
  selectNode(nodeId) {
    this.#addNodeSelectionId(nodeId);
    this.#updateDialogSelectButton();
    // check in case the node does not exist anymore (Input::withValue()).
    if (this.#nodeMap.has(nodeId)) {
      const node = this.#nodeMap.get(nodeId);
      toggleSelectedNodeElementClass(node.element, true);
      this.#changeNodeSelectButtonToUnselect(node.selectButton, node.name);
      this.#renderNodeSelectionListEntry(node);
      this.#renderNodeDropdownMenuEntry(node);
      this.#updateNodeDropdownCounter();
      this.#updateNodeDropdownMenuEntryPlaceholder();
    }
    this.#selectionHandler(this);
  }

  /**
   * @param {string} nodeId
   */
  engageNode(nodeId) {
    if (!this.#nodeMap.has(nodeId)) {
      return;
    }
    const parentLevel = this.#nodeMap.get(nodeId).drilldownParentLevel;
    if (this.#drilldownComponent.getCurrentLevel() !== parentLevel
      && this.#drilldownComponent.getParentLevel() !== parentLevel
    ) {
      this.#drilldownComponent.engageLevel(parentLevel);
    }
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
    const node = this.#nodeMap.get(engagedNodeId);
    this.#removeAllBreadcrumbs();
    this.#renderAllBreadcrumbs(node);
    if (node.renderUrl !== null) {
      this.#renderAsyncNodeChildren(node);
    }
  }

  /**
   * @param {HTMLLIElement} listEntry
   * @param {string} nodeId
   */
  #addRemoveNodeListEntryClickHandler(listEntry, nodeId) {
    listEntry
      .querySelector(CONSTANTS.REMOVE_ACTION)
      ?.addEventListener('click', () => {
        this.unselectNode(nodeId);
        listEntry.remove();
      });
  }

  /**
   * @param {HTMLLIElement} nodeDropdownEntry
   * @param {string} nodeId
   */
  #addEngageNodeDropdownEntryClickHandler(nodeDropdownEntry, nodeId) {
    nodeDropdownEntry.querySelector(CONSTANTS.ENGAGE_ACTION)?.addEventListener('click', () => {
      this.engageNode(nodeId);
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
  #renderNodeSelectionListEntry(node) {
    this.#renderNodeListEntry(
      node,
      this.#nodeSelectionElement,
      this.#nodeSelectionEntryTemplate,
      (newEntry) => {
        this.#addRemoveNodeListEntryClickHandler(newEntry, node.id);
      },
    );
  }

  /**
   * @param {TreeSelectNode} node
   */
  #renderNodeDropdownMenuEntry(node) {
    if (this.#nodeDropdownMenuElement === null || this.#nodeDropdownMenuEntryTemplate === null) {
      return;
    }
    this.#renderNodeListEntry(
      node,
      this.#nodeDropdownMenuElement,
      this.#nodeDropdownMenuEntryTemplate,
      (newEntry) => {
        this.#addRemoveNodeListEntryClickHandler(newEntry, node.id);
        this.#addEngageNodeDropdownEntryClickHandler(newEntry, node.id);
      },
    );
  }

  /**
   * @param {TreeSelectNode} node
   * @param {HTMLTemplateElement} template
   * @param {HTMLUListElement} list
   * @param {function(HTMLLIElement)} hydrator
   */
  #renderNodeListEntry(node, list, template, hydrator) {
    if (list.querySelector(`li[${CONSTANTS.NODE_ID}="${node.id}"]`) !== null) {
      return;
    }
    const newEntryFragment = this.#templateRenderer.createContent(template);
    const newEntryElement = newEntryFragment.querySelector(`[${CONSTANTS.NODE_ID}]`);

    newEntryElement.setAttribute(CONSTANTS.NODE_ID, node.id);
    newEntryElement.querySelector(`[${CONSTANTS.NODE_NAME}]`).textContent = node.name;

    // set input value if present
    const newEntryInput = newEntryElement.querySelector('input');
    if (newEntryInput) {
      newEntryInput.value = node.id;
    }

    // set action aria-labels if present
    newEntryElement.querySelector(CONSTANTS.REMOVE_ACTION)?.setAttribute('aria-label', this.#translate('select_node', node.name));
    newEntryElement.querySelector(CONSTANTS.ENGAGE_ACTION)?.setAttribute('aria-label', this.#translate('engage_node', node.name));

    list.append(newEntryElement);
    hydrator(newEntryElement);
  }

  /**
   * @param {string} nodeId
   */
  #removeNodeSelectionListEntry(nodeId) {
    removeNodeListEntry(this.#nodeSelectionElement, nodeId);
  }

  /**
   * @param {string} nodeId
   */
  #removeNodeDropdownMenuEntry(nodeId) {
    if (this.#nodeDropdownMenuElement !== null) {
      removeNodeListEntry(this.#nodeDropdownMenuElement, nodeId);
    }
  }

  /**
   * @param {TreeSelectNode} node
   */
  #hydrateNode(node) {
    this.#addNodeSelectButtonClickHandler(node.selectButton, node);
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

  #updateNodeDropdownMenuEntryPlaceholder() {
    if (this.#nodeSelectionSet.size <= 0) {
      this.#nodeDropdownMenuEntryPlaceholder?.classList.remove(CONSTANTS.HIDDEN_CLASS);
    } else {
      this.#nodeDropdownMenuEntryPlaceholder?.classList.add(CONSTANTS.HIDDEN_CLASS);
    }
  }

  #updateNodeDropdownCounter() {
    if (this.#nodeDropdownCounter !== null) {
      this.#nodeDropdownCounter.setStatusTo(this.#nodeSelectionSet.size);
    }
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
