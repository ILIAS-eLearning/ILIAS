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

import TreeSelect from './TreeSelect.js';
import TemplateRenderer from '../../../../Core/src/TemplateRenderer.js';
import AsyncRenderer from '../../../../Core/src/AsyncRenderer.js';
import updateSingleSelectButtonStates from './updateSingleSelectButtonStates.js';
import updateMultiSelectButtonStates from './updateMultiSelectButtonStates.js';
import engageParentDrilldownLevel from './engageParentDrilldownLevel.js';
import createTreeSelectNodes from './createTreeSelectNodes.js';
import unselectChildNodes from './unselectChildNodes.js';
import * as CONSTANTS from './constants.js';

/**
 * @param {HTMLElement} element
 * @returns {HTMLLIElement[]}
 */
function getNodeElements(element) {
  return Array.from(element.querySelectorAll(CONSTANTS.NODE));
}

/**
 * @returns {function(TreeSelect): void}
 */
function getSingleSelectionHandler() {
  return (treeSelectComponent) => {
    updateSingleSelectButtonStates(treeSelectComponent);
    engageParentDrilldownLevel(treeSelectComponent);
  };
}

/**
 * @param {boolean} canSelectSubNodes
 * @returns {function(TreeSelect): void}
 */
function getMultiSelectionHandler(canSelectSubNodes) {
  if (canSelectSubNodes) {
    return () => {};
  }
  return (treeSelectComponent) => {
    unselectChildNodes(treeSelectComponent);
    updateMultiSelectButtonStates(treeSelectComponent);
  };
}

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
export default class TreeSelectFactory {
  /** @type {Map<string, TreeSelect>} */
  #instances = new Map();

  /** @type {JQueryEventListener} */
  #jqueryEventListener;

  /** @type {DrilldownFactory} */
  #drilldownFactory;

  /** @type {{ getCounterObject(HTMLElement): counterObject }} */
  #counterFactory;

  /** @type {{txt: function(string): string}} */
  #language;

  /** @type {Document} */
  #document;

  /**
   * @param {JQueryEventListener} jqueryEventListener
   * @param {DrilldownFactory} drilldownFactory
   * @param {{ getCounterObject(HTMLElement): counterObject }}
   * @param {{txt: function(string): string}} language
   * @param {Document} document
   */
  constructor(
    jqueryEventListener,
    drilldownFactory,
    counterFactory,
    language,
    document,
  ) {
    this.#jqueryEventListener = jqueryEventListener;
    this.#drilldownFactory = drilldownFactory;
    this.#counterFactory = counterFactory;
    this.#language = language;
    this.#document = document;
  }

  /**
   * @param {string} inputId
   * @param {boolean} canSelectChildNodes
   * @throws {Error} if elements are not found
   */
  initTreeMultiSelect(inputId, canSelectChildNodes) {
    if (this.#instances.has(inputId)) {
      throw new Error(`TreeSelect '${inputId}' already exists.`);
    }

    const [
      dialogOpenButton,
      treeSelectElement,
      breadcrumbsElement,
      breadcrumbTemplate,
      nodeSelectionElement,
      nodeSelectionTemplate,
      dialogElement,
      dialogSelectButton,
    ] = this.#getTreeSelectElements(inputId);

    const [
      nodeDropdownElement,
      nodeDropdownMenuElement,
      nodeDropdownMenuEntryTemplate,
      nodeDropdownMenuEntryPlaceholder,
    ] = this.#getTreeMultiSelectElements(inputId);

    const drilldownComponent = this.#getDrilldown(treeSelectElement);
    const counterComponent = this.#getCounter(nodeDropdownElement);

    const treeMultiSelect = new TreeSelect(
      createTreeSelectNodes(getNodeElements(dialogElement)),
      this.#jqueryEventListener,
      new TemplateRenderer(this.#document),
      new AsyncRenderer(this.#document),
      this.#language,
      drilldownComponent,
      breadcrumbsElement,
      breadcrumbTemplate,
      nodeSelectionElement,
      nodeSelectionTemplate,
      dialogSelectButton,
      dialogOpenButton,
      dialogElement,
      getMultiSelectionHandler(canSelectChildNodes),
      nodeDropdownMenuElement,
      counterComponent,
      nodeDropdownMenuEntryTemplate,
      nodeDropdownMenuEntryPlaceholder,
    );

    this.#instances.set(inputId, treeMultiSelect);

    return treeMultiSelect;
  }

  /**
   * @param {string} inputId
   * @throws {Error} if elements are not found
   */
  initTreeSelect(inputId) {
    if (this.#instances.has(inputId)) {
      throw new Error(`TreeSelect '${inputId}' already exists.`);
    }

    const [
      dialogOpenButton,
      treeSelectElement,
      breadcrumbsElement,
      breadcrumbTemplate,
      nodeSelectionElement,
      nodeSelectionEntryTemplate,
      dialogElement,
      dialogSelectButton,
    ] = this.#getTreeSelectElements(inputId);

    const drilldownComponent = this.#getDrilldown(treeSelectElement);

    const treeSelect = new TreeSelect(
      createTreeSelectNodes(getNodeElements(dialogElement)),
      this.#jqueryEventListener,
      new TemplateRenderer(this.#document),
      new AsyncRenderer(this.#document),
      this.#language,
      drilldownComponent,
      breadcrumbsElement,
      breadcrumbTemplate,
      nodeSelectionElement,
      nodeSelectionEntryTemplate,
      dialogSelectButton,
      dialogOpenButton,
      dialogElement,
      getSingleSelectionHandler(),
    );

    this.#instances.set(inputId, treeSelect);

    return treeSelect;
  }

  /**
   * @param {string} inputId
   * @returns {TreeSelect|null}
   */
  getInstance(inputId) {
    if (this.#instances.has(inputId)) {
      return this.#instances.get(inputId);
    }
    return null;
  }

  /**
   * @param {string} inputId
   * @returns {HTMLElement[]}
   * @throws {Error} if elements are not found
   */
  #getTreeSelectElements(inputId) {
    const dialogOpenButton = this.#document.getElementById(inputId)
      ?.querySelector(CONSTANTS.TREE_OPEN_DIALOG_BUTTON);
    const treeSelectElement = dialogOpenButton?.closest(CONSTANTS.TREE_SELECT);
    const breadcrumbsElement = treeSelectElement?.querySelector(CONSTANTS.BREADCRUMB);
    const breadcrumbTemplate = treeSelectElement?.querySelector('.modal-body > template');
    const nodeSelectionElement = treeSelectElement?.querySelector(CONSTANTS.TREE_SELECT_SELECTION);
    const nodeSelectionTemplate = nodeSelectionElement?.querySelector(':scope > template');
    const dialogElement = treeSelectElement?.querySelector('dialog');
    const dialogSelectButton = dialogElement?.querySelector(CONSTANTS.TREE_SELECT_BUTTON);

    if (!breadcrumbsElement
      || !breadcrumbTemplate
      || !nodeSelectionElement
      || !nodeSelectionTemplate
      || !dialogSelectButton
      || !dialogOpenButton
      || !dialogElement
    ) {
      throw new Error(`Could not find some element(s) for Tree Select Input '${inputId}'.`);
    }

    return [
      dialogOpenButton,
      treeSelectElement,
      breadcrumbsElement,
      breadcrumbTemplate,
      nodeSelectionElement,
      nodeSelectionTemplate,
      dialogElement,
      dialogSelectButton,
    ];
  }

  /**
   * @param {string} inputId
   * @returns {HTMLElement[]}
   * @throws {Error} if elements are not found
   */
  #getTreeMultiSelectElements(inputId) {
    const dialogOpenButton = this.#document.getElementById(inputId)
      ?.querySelector(CONSTANTS.TREE_OPEN_DIALOG_BUTTON);
    const treeSelectElement = dialogOpenButton?.closest(CONSTANTS.TREE_SELECT);
    const dropdownElement = treeSelectElement?.querySelector(CONSTANTS.TREE_SELECT_DROPDOWN);
    const dropdownMenuElement = treeSelectElement
      ?.querySelector(CONSTANTS.TREE_SELECT_DROPDOWN_MENU);
    const dropdownMenuEntryTemplate = dropdownMenuElement?.querySelector(':scope > template');
    const dropdownMenuEntryPlaceholder = dropdownMenuElement
      ?.querySelector(`:scope > ${CONSTANTS.TREE_SELECT_DROPDOWN_ENTRY_PLACEHODLER}`);

    if (!dropdownElement
      || !dropdownMenuElement
      || !dropdownMenuEntryTemplate
      || !dropdownMenuEntryPlaceholder
    ) {
      throw new Error(`Could not find some element(s) for Tree Multi Select Input '${inputId}'.`);
    }

    return [
      dropdownElement,
      dropdownMenuElement,
      dropdownMenuEntryTemplate,
      dropdownMenuEntryPlaceholder,
    ];
  }

  /**
   * @param {HTMLDivElement} element
   * @returns {Drilldown}
   * @throws {Error} if instance can not be found
   */
  #getDrilldown(element) {
    const drilldownElement = element.querySelector(CONSTANTS.DRILLDOWN);
    if (drilldownElement === null || !drilldownElement.hasAttribute('id')) {
      throw new Error('Could not find drilldown element.');
    }
    const drilldownComponent = this.#drilldownFactory.getInstance(drilldownElement.id);
    if (drilldownElement === null) {
      throw new Error('Could not find drilldown instance.');
    }
    return drilldownComponent;
  }

  /**
   * @param {HTMLElement} element
   * @returns {counterObject}
   * @throws {Error} if instance can not be found
   */
  #getCounter(element) {
    const counterComponent = this.#counterFactory.getCounterObject(element);
    if (!counterComponent) {
      throw new Error('Could not find counter instance.');
    }
    return counterComponent;
  }
}
