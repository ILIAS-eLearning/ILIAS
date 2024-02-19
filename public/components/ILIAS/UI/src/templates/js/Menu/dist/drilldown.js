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
(function (il$1, document$1, $) {
  'use strict';

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

  class Drilldown {
    /**
     * @type {DrilldownPersistence}
     */
    #persistence;

    /**
     * @type {DrilldownModel}
     */
    #model;

    /**
     * @type {DrilldownMapping}
     */
    #mapping;

    /**
     * @param {jQuery} $
     * @param {DrilldownPersistence} persistence
     * @param {DrilldownModel} model
     * @param {DrilldownMapping} mapping
     * @param {string} backSignal
     */
    constructor($, persistence, model, mapping, backSignal) {
      this.#persistence = persistence;
      this.#model = model;
      this.#mapping = mapping;

      $(document).on(backSignal, () => { this.#upLevel(); });
      this.#mapping.setFilterHandler(
        (e) => {
          this.#filter(e);
        },
      );
      this.#mapping.parseLevel(
        (headerDisplayElement, parent, leaves) => this.#model
          .addLevel(headerDisplayElement, parent, leaves),
        (index, text) => {
          return this.#model.buildLeaf(index, text);
        },
        (levelId) => {
          this.#engageLevel(levelId);
        }
      );

      this.#engageLevel(this.#persistence.read());
    }

    /**
     *
     * @param {integer} levelId
     * @returns {void}
     */
    #engageLevel(levelId) {
      this.#model.engageLevel(levelId);
      this.#apply();
    }

    /**
     * @param {Event} e
     * @returns {void}
     */
    #filter(e) {
      this.#model.filter(e);
      this.#mapping.setFiltered(this.#model.getFiltered());
      e.target.focus();
    }

    /**
     * @returns {void}
     */
    #upLevel() {
      this.#model.upLevel();
      this.#apply();
    }

    /**
     * @returns {void}
     */
    #apply() {
      const current = this.#model.getCurrent();
      const parent = this.#model.getParent();
      let level = 2;
      if (current.parent === null) {
        level = 0;
      } else if (current.parent === '0') {
        level = 1;
      }
      this.#mapping.setEngaged(current.id);
      this.#persistence.store(current.id);
      this.#mapping.setHeader(current.headerDisplayElement, parent.headerDisplayElement);
      this.#mapping.setHeaderBacknav(level);
      this.#mapping.correctRightColumnPositionAndHeight(current.id);
    }
  }

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

  class DrilldownPersistence {
    /**
       * @type {string}
       */
    #key = 'level_id';

    /**
       * @type {object}
       */
    #cookieStorage;

    /**
       * @param {string} persistenceId
       */
    constructor(cookieStorage) {
      this.#cookieStorage = cookieStorage;
    }

    #storage() {
      return this.#cookieStorage;
    }

    /**
       * @returns {string}
       */
    read() {
      return this.#cookieStorage.items[this.#key] ?? 0;
    }

    /**
       *
       * @param {string} level_id
       * @returns void
       */
    store(levelId) {
      this.#cookieStorage.add(this.#key, levelId);
      this.#cookieStorage.store();
    }
  }

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

  class DrilldownModel {
    /**
     * @type {object}
     */
    #level = {
      id: null,
      parent: null,
      engaged: false,
      headerDisplayElement: '',
      leaves: [],
    };

    /**
     * @type {object}
     */
    #leaf = {
      index: null,
      text: null,
      filtered: false,
    };

    /**
     * @type {this.#level[]}
     */
    #data = [];

    /**
     * @param {string} levelId
     * @param {HTMLButtonElement} headerDisplayElement
     * @param {int} parent
     * @param {array} leaves
     * @returns {this.#level}
     */
    #buildLevel(levelId, headerDisplayElement, parent, leaves) {
      const level = { ...this.#level };
      level.id = levelId;
      level.parent = parent;
      level.headerDisplayElement = headerDisplayElement;
      level.leaves = leaves;
      return level;
    }

    buildLeaf(index, text) {
      const leaf = { ...this.#leaf };
      leaf.index = index;
      leaf.text = text;
      return leaf;
    }

    /**
     * @param {HTMLButtonElement} headerDisplayElement
     * @param {int} parent
     * @param {array} leaves
     * @returns {this.#level}
     */
    addLevel(headerDisplayElement, parent, leaves) {
      const levelId = this.#data.length.toString();
      const level = this.#buildLevel(levelId, headerDisplayElement, parent, leaves);
      this.#data[level.id] = level;
      return this.#data[level.id];
    }

    /**
     * @param  {String} levelId
     */
    engageLevel(levelId) {
      this.#data.forEach(
        (level) => {
          const levelRef = level;
          levelRef.engaged = false;
          if (level.id === levelId) {
            levelRef.engaged = true;
          }
        },
      );
    }

    /**
     * @returns {this.#level}
     */
    getCurrent() {
      const cur = this.#data.find(
        (level) => level.engaged,
      );
      if (cur !== undefined) {
        return cur;
      }
      return this.#data[0];
    }

    /**
     * @returns {integer}
     */
    getParent() {
      const cur = this.getCurrent();
      if (cur.parent) {
        return this.#data[cur.parent];
      }
      return {};
    }

    /**
     * @return {void}
     */
    upLevel() {
      const cur = this.getCurrent();
      if (cur.parent) {
        this.engageLevel(this.#data[cur.parent].id);
      }
    }

    /**
     * @param {integer} levelId
     * @return {void}
     */
    #removeFilteredRecursive(levelId) {
      if (levelId !== null && levelId !== 0) {
        return;
      }

      this.#data[levelId].filtered = false;
      if (this.#data[levelId].parent !== null && this.#data[levelId].parent !== 0) {
        this.#removeFilteredRecursive(this.#data[levelId].parent);
      }
    }

    /**
     * @param {Event} e
     * @returns {void}
     */
    filter(e) {
      const value = e.target.value.toLowerCase();
      this.#data.forEach(
        (level) => {
          const levelRef = level;
          levelRef.leaves.forEach(
            (leaf) => {
              const leafRef = leaf;
              if (value === '') {
                leafRef.filtered =  false;
                return;
              }
              if (leafRef.text.toLowerCase().includes(value) === false) {
                leafRef.filtered = true;
                return;
              }
              leafRef.filtered = false;
            },
          );
        },
      );
    }

    /**
     * @returns {this.#level[]}
     */
    getFiltered() {
      const filtered = [];
      this.#data.forEach(
        (level) => {
          const leaves = level.leaves.filter(
            (leaf) => leaf.filtered,
          );
          if (leaves.length > 0) {
            const clone = this.#buildLevel(
              level.id,
              level.headerDisplayElement,
              level.parent,
              [...leaves],
            );
            filtered.push(clone);
          }
        },
      );
      return filtered;
    }
  }

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

  class DropdownMapping {
    /**
       * @type {object}
       */
    #classes = {
      DRILLDOWN: 'c-drilldown',
      MENU: 'c-drilldown__menu',
      MENU_FILTERED: 'c-drilldown--filtered',
      HEADER_ELEMENT: 'c-drilldown__menulevel--trigger',
      MENU_BRANCH: 'c-drilldown__branch',
      MENU_LEAF: 'c-drilldown__leaf',
      FILTER: 'c-drilldown__filter',
      MENU_FILTERED: 'c-drilldown--filtered',
      ACTIVE: 'c-drilldown__menulevel--engaged',
      ACTIVE_ITEM: 'c-drilldown__menuitem--engaged',
      ACTIVE_PARENT: 'c-drilldown__menulevel--engagedparent',
      FILTERED: 'c-drilldown__menuitem--filtered',
      WITH_BACKLINK_ONE_COL: 'c-drilldown__header--showbacknav',
      WITH_BACKLINK_TWO_COL: 'c-drilldown__header--showbacknavtwocol',
      HEADER_TAG: 'header',
      LIST_TAG: 'ul',
      LIST_ELEMENT_TAG: 'li',
      ID_ATTRIBUTE: 'data-ddindex',
    };

    /**
       * @type {object}
       */
    #elements = {
      dd: null,
      header: null,
      levels: [],
    };

    /**
       * @type {function}
       */
    #document;

    /**
       * @param {DOMDocument} document
       * @param {string} dropdownId
       */
    constructor(document, dropdownId) {
      this.#document = document;
      this.#elements.dd = document.getElementById(dropdownId);
      [this.#elements.header] = this.#elements.dd.getElementsByTagName(this.#classes.HEADER_TAG);
    }

    /**
       * @returns {HTMLUnorderedListElement}
       */
    #getMenuContainer() {
      return this.#elements.dd.querySelector(`.${this.#classes.MENU}`);
    }

    /**
     * @param {function} filterHandler
     * @return {void}
     */
    setFilterHandler(filterHandler) {
      this.#elements.header.querySelector(`.${this.#classes.FILTER} > input`).addEventListener('keyup', filterHandler);
    }

    /**
       * @param {function} filterHandler
       * @return {void}
       */
    parseLevel(levelRegistry, leafBuilder, clickHandler) {
      const sublists = this.#getMenuContainer().querySelectorAll(this.#classes.LIST_TAG);
      sublists.forEach(
        (sublist) => {
          const level = levelRegistry( // from model
            this.#getLabelForList(sublist),
            this.#getParentIdOfList(sublist),
            this.#getLeavesOfList(sublist, leafBuilder),
          );
          this.#addLevelId(sublist, level.id);
          this.#registerHandler(sublist, clickHandler, level.id);
          this.#elements.levels[level.id] = sublist;
        },
      );
    }

    /**
       * @param {HTMLListElement} list
       * @param {string} levelId
       * @returns {void}
       */
    #addLevelId(list, levelId) {
      const listRef = list;
      listRef.setAttribute(this.#classes.ID_ATTRIBUTE, levelId);
    }

    /**
       * @param {HTMLListElement} list
       * @param {function}
       * @return {HTMLElement}
       */
    #getLabelForList(list) {
      const element = list.previousElementSibling;
      if (element === null) {
        return null;
      }
      let elem = null;
      elem = this.#document.createElement('h2');
      elem.innerText = element.childNodes[0].nodeValue;
      return elem;
    }

    /**
       * @param {HTMLListElement} list
       * @returns {string}
       */
    #getParentIdOfList(list) {
      return list.parentElement.parentElement.getAttribute(this.#classes.ID_ATTRIBUTE);
    }

    /**
       * @param {HTMLListElement} list
       * @return {object}
       */
    #getLeavesOfList(list, leafBuilder) {
      const leaf_elements = list.querySelectorAll(`:scope >.${this.#classes.MENU_LEAF}`);
      const leaves = [];
      leaf_elements.forEach(
        (leaf_element, index) => {
          leaves.push(
            leafBuilder(
              index,
              leaf_element.firstElementChild.innerText
            )
          );
        },
      );
      return leaves;
    }

    /**
       * @param {HTMLListElement} list
       * @param {function} handler
       * @param {string} elementId
       * @returns {void}
       */
    #registerHandler(list, handler, elementId) {
      const headerElement = list.previousElementSibling;
      if (headerElement=== null) {
        return;
      }
      headerElement.addEventListener('click', () => { handler(elementId); });
    }

    /**
       * @param {string} elementId
       * @return {void}
       */
    setEngaged(elementId) {
      this.#elements.dd.querySelector(`.${this.#classes.ACTIVE}`)
        ?.classList.remove(`${this.#classes.ACTIVE}`);
      this.#elements.dd.querySelector(`.${this.#classes.ACTIVE_ITEM}`)
        ?.classList.remove(`${this.#classes.ACTIVE_ITEM}`);
      this.#elements.dd.querySelector(`.${this.#classes.ACTIVE_PARENT}`)
        ?.classList.remove(`${this.#classes.ACTIVE_PARENT}`);

      const activeLevel = this.#elements.levels[elementId];
      activeLevel.classList.add(this.#classes.ACTIVE);
      const parentLevel = activeLevel.parentElement.parentElement;
      if (parentLevel.nodeName === 'UL') {
        activeLevel.parentElement.classList.add(this.#classes.ACTIVE_ITEM);
        parentLevel.classList.add(this.#classes.ACTIVE_PARENT);
      } else {
        activeLevel.classList.add(this.#classes.ACTIVE_PARENT);
      }

      if (activeLevel.parentElement !== null) ;

      const lower = this.#elements.levels[elementId].children[0].children[0];
      lower.focus();
    }

    /**
     * @param {object[]} filteredElements
     * @return {void}
     */
    setFiltered(filteredItems) {
      const levels = this.#elements.dd.querySelectorAll(`${this.#classes.LIST_TAG}`);
      const leaves = this.#elements.dd.querySelectorAll(`.${this.#classes.MENU_LEAF}`);
      const filteredItemsIds = filteredItems.map((v) => v.id);
      const topLevelItems = this.#elements.dd.querySelectorAll(
        `.${this.#classes.MENU} > ul > .${this.#classes.MENU_BRANCH}`
      );

      leaves.forEach(
        (element) => {
          const elemRef = element;
          elemRef.classList.remove(this.#classes.FILTERED);
        }
      );

      if (filteredItems.length === 0) {
        this.#elements.dd.classList.remove(this.#classes.MENU_FILTERED);
        topLevelItems.forEach(
          (element) => {
            const elemRef = element;
            elemRef.firstElementChild.disabled = false;
            elemRef.classList.remove(this.#classes.FILTERED);
          }
        );
        return;
      }

      this.setEngaged(0);
      this.#elements.dd.classList.add(this.#classes.MENU_FILTERED);
      topLevelItems.forEach(
        (element) => {
          const elemRef = element;
          elemRef.firstElementChild.disabled = true;
          elemRef.classList.remove(this.#classes.FILTERED);
        },
      );

      filteredItemsIds.forEach(
        (id, index) => {
          const [element] = [...levels].filter((level) => level.getAttribute(this.#classes.ID_ATTRIBUTE) === id);
          const element_children = element.querySelectorAll(`:scope >.${this.#classes.MENU_LEAF}`);
          filteredItems[index].leaves.forEach(
            (leaf) => element_children[leaf.index].classList.add(this.#classes.FILTERED)
          );
        }
      );

      topLevelItems.forEach(
        (element) => {
          const filtered_elements = element.querySelectorAll(
            `.${this.#classes.MENU_LEAF}:not(.${this.#classes.FILTERED})`
          );
          if (filtered_elements.length === 0) {
            const elemRef = element;
            elemRef.classList.add(this.#classes.FILTERED);
          }
        }
      );
    }

    /**
     * @param {HTMLElement} headerElement
     * @param {HTMLElement} headerParentElement
     * @return {void}
     */
    setHeader(headerElement, headerParentElement) {
      this.#elements.header.children[1].replaceWith(this.#document.createElement('div'));
      if (headerElement === null) {
        this.#elements.header.firstElementChild.replaceWith(this.#document.createElement('div'));
        return;
      }
      this.#elements.header.firstElementChild.replaceWith(headerElement);
      if (headerParentElement !== null) {
        this.#elements.header.children[1].replaceWith(headerParentElement);
        return;
      }
    }

    /**
     * @param {integer} level
     * @return {void}
     */
    setHeaderBacknav(level) {
      this.#elements.header.classList.remove(this.#classes.WITH_BACKLINK_TWO_COL);
      this.#elements.header.classList.remove(this.#classes.WITH_BACKLINK_ONE_COL);
      if (level === 0) {
        return;
      }
      if (level > 1) {
        this.#elements.header.classList.add(this.#classes.WITH_BACKLINK_TWO_COL);
      }
      this.#elements.header.classList.add(this.#classes.WITH_BACKLINK_ONE_COL);
    }

    /**
     * @param {integer} levelId
     * @return {void
     */
    correctRightColumnPositionAndHeight(levelId) {
      var elem = this.#elements.levels[levelId];
      const menu = this.#elements.dd.querySelector(`.${this.#classes.MENU}`);
      const height = this.#elements.dd.querySelector(`.${this.#classes.MENU}`).offsetHeight;
      if (height === 0) {
        const triggerResize = new ResizeObserver((element) => {
          if (element[0].target.offsetHeight > 0) {
            this.correctRightColumnPositionAndHeight(levelId);
            triggerResize.unobserve(menu);
          }
        });
        triggerResize.observe(menu);
        return;
      }
      this.#elements.levels.forEach(
        (e) => {
          const eRef = e;
          eRef.style.removeProperty('top');
          eRef.style.removeProperty('height');
        },
      );
      if (levelId  === '0') {
        elem = elem.querySelector(`:scope > .${this.#classes.MENU_BRANCH} > ul`);
      }
      if (elem.offsetHeight === 0) {
        return;
      }
      elem.style.top = `-${elem.offsetTop}px`;
      elem.style.height = height +'px';
    }
  }

  class DrilldownFactory {
    /**
     * @type {Array<string, Drilldown>}
     */
    #instances = [];

    /**
     * @type {DOMDocument}
     */
    #document;

    /**
     * @param {DOMDocument} document
     */
    construct(document) {
      this.#document = document;
    }

    /**
     * @param {string} drilldownId
     * @param {string} backSignal
     * @param {string} persistanceId
     * @return {void}
     * @throws {Error} if the input was already initialized.
     */
    init(drilldownId, backSignal, persistanceId) {
      if (undefined !== this.#instances[drilldownId]) {
        throw new Error(`Drilldown with id '${drilldownId}' has already been initialized.`);
      }

      this.#instances[drilldownId] = new Drilldown(
        $,
        new DrilldownPersistence(new il.Utilities.CookieStorage(persistanceId)),
        new DrilldownModel(),
        new DropdownMapping(document, drilldownId),
        backSignal,
      );
    }

    /**
     * @param {string} drilldownId
     * @param {Drilldown|null}
     */
    get(drilldownId) {
      return this.#instances[drilldownId] ?? null;
    }
  }

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


  il$1.UI = il$1.UI || {};
  il$1.UI.menu = il$1.UI.menu || {};
  il$1.UI.menu.drilldown = new DrilldownFactory(document$1);

})(il, document, $);
