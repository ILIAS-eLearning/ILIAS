il = il || {};
il.UI = il.UI || {};

(function($, UI) {
	UI.tree = (function($) {
		var toogle_node_actions = [];

		this.init = function (component_id, highlight_nodes) {
			var tree_dom = document.querySelector('#' + component_id);
			initNodesForActions($(tree_dom));
			var tree = new TreeLinks(tree_dom);
			tree.init();
		}

		this.registerToggleNodeAsyncAction = function (id, action, state_param) {
			action += (action.indexOf("?") !== -1 ? "&" : "?") + encodeURIComponent(state_param) + "=";
			toogle_node_actions[id] = action;
		}

		this.toggleNodeState = function (id, was_expanded) {
			var action = toogle_node_actions[id]+was_expanded;

			$.ajax({
				type: 'POST',
				url: action
			});
		}

		/**
		 * Interface returned by this function for public use (see return statement bellow)
		 * The contained functions are implemented bellow
		 */
		var public_interface = {
			init: this.init,
			registerToggleNodeAsyncAction: this.registerToggleNodeAsyncAction,
			toggleNodeState: this.toggleNodeState
		}

		/**
		 * @private
		 */
		var initNodesForActions = function (tree_dom) {
			tree_dom.find('.c-tree__node .c-tree__node__line .tree__node__line a').click(
				function(e) {
					let href = $(this).attr('href');

					if (typeof href === typeof undefined || href === false || href === "#") {
						return false;
					}

					// Don't propagate event to prevent expanding the node on click
					e.stopPropagation();
				}
			);
		}

		/**
		 * The following section contains helper functions and scopes to deal with the keyboard handling as specified in:
		 * https://www.w3.org/TR/wai-aria-practices/examples/treeview/treeview-2/treeview-2a.html . The code has been
		 * heavily adapted to fit into the context of trees in ILIAS including async loading. The original version is licensed
		 * according to the W3C Software License at https://www.w3.org/Consortium/Legal/2015/copyright-software-and-document
		 *
		 * Note that the in the following code is only used internally by UI.tree
		 */

		/**
		 * @private
		 */
		var TreeLinks = function (node) {
			this.domNode = node;

			this.treeitems = [];
			this.firstChars = [];

			this.firstTreeitem = null;
			this.lastTreeitem = null;

		};

		TreeLinks.prototype.init = function () {
			// Check whether node is a DOM element
			if (!this.domNode) {
				return;
			}
			let items = [];
			let chars = [];
			this.findAndInitItems(this.domNode, this, false, items, chars);
			this.treeitems = items;
			this.firstChars = chars;

			this.updateVisibleTreeitems();

			this.firstTreeitem.domNode.tabIndex = 0;

		};

		TreeLinks.prototype.insert = function (self, currentItem) {
			let items = [];
			let chars = [];
			self.findAndInitItems(currentItem.domNode, self, false,items,chars);
			let current_index = self.getIndexOfItem(self,currentItem);

			items.forEach(function(item,id){
				self.treeitems.splice(current_index+1,0,item);
				self.firstChars.splice(current_index+1,0,chars[id]);
				current_index++;
			});
		};

		TreeLinks.prototype.findAndInitItems = function findTreeitems (node, tree, group,items,chars)  {
			var elem = node.firstElementChild;
			var ti = group;

			while (elem) {
				let is_valid_list_item = (elem.tagName.toLowerCase() === 'li'
					&& elem.firstElementChild.tagName.toLowerCase() === 'span'
					&& elem.getAttribute("role")!="none");
				let is_link = elem.tagName.toLowerCase() === 'a';
				if (is_valid_list_item || is_link) {
					ti = new TreeitemLink(elem, tree, group);
					ti.init();
					items.push(ti);
					chars.push(ti.label.substring(0, 1).toLowerCase());
				}

				if (elem.firstElementChild) {
					tree.findAndInitItems(elem, tree, ti,items,chars);
				}

				elem = elem.nextElementSibling;
			}
		}

		TreeLinks.prototype.getIndexOfItem = function (tree, treeitem){
			let index_of_item = undefined;
			tree.treeitems.forEach(function(item, id){
				if (item === treeitem) {
					index_of_item = id;
				}
			});
			return index_of_item;
		}


		TreeLinks.prototype.setFocusToItem = function (treeitem) {
			this.treeitems.forEach(function(item, id){
				if (item === treeitem) {
					item.domNode.tabIndex = 0;
					item.domNode.focus();
				}
				else {
					item.domNode.tabIndex = -1;
				}
			});
		};

		TreeLinks.prototype.setFocusToNextItem = function (currentItem) {
			let next_index = this.getIndexOfItem(this,currentItem)+1;
			while(next_index < this.treeitems.length){
				let nextItem = this.treeitems[next_index];
				if(nextItem.isVisible){
					this.setFocusToItem(nextItem);
					return;
				}
				next_index++;
			}
		};

		TreeLinks.prototype.setFocusToPreviousItem = function (currentItem) {
			let prev_index = this.getIndexOfItem(this,currentItem)-1;
			while(prev_index >= 0){
				let prevItem = this.treeitems[prev_index];
				if(prevItem.isVisible){
					this.setFocusToItem(prevItem);
					return;
				}
				prev_index--;
			}
		};

		TreeLinks.prototype.setFocusToParentItem = function (currentItem) {
			if (currentItem.groupTreeitem) {
				this.setFocusToItem(currentItem.groupTreeitem);
			}
		};

		TreeLinks.prototype.setFocusToFirstItem = function () {
			this.setFocusToItem(this.firstTreeitem);
		};

		TreeLinks.prototype.setFocusToLastItem = function () {
			this.setFocusToItem(this.lastTreeitem);
		};

		TreeLinks.prototype.expandTreeitem = function (currentItem) {
			if (currentItem.isExpandable) {
				currentItem.domNode.setAttribute('aria-expanded', true);
				if(currentItem.domNode.getAttribute('data-async_loaded') === "false"){
					this.loadSubTreeAsync(this,currentItem);
				}else{
					this.updateVisibleTreeitems();
				}
				il.UI.tree.toggleNodeState(currentItem.domNode.id,0);
			}
		};

		TreeLinks.prototype.loadSubTreeAsync = function (tree, currentItem){
			$.ajax({
				url: currentItem.domNode.getAttribute('data-async_url'),
				dataType: 'html'
			}).done(
				function(html) {
					currentItem.domNode.setAttribute('data-async_loaded', "true");

					if(!html) {
						currentItem.domNode.classList.remove('expandable');
						currentItem.domNode.removeAttribute('data-async_loaded');
					}
					$(currentItem.domNode).children('ul').append(html);
					tree.insert(tree, currentItem);
					tree.updateVisibleTreeitems();
				}
			);
		}

		TreeLinks.prototype.expandAllSiblingItems = function (currentItem) {
			let self = this;
			self.treeitems.forEach(function(item){
				if ((item.groupTreeitem === currentItem.groupTreeitem) && item.isExpandable) {
					self.expandTreeitem(item);
				}
			});
		};

		TreeLinks.prototype.collapseTreeitem = function (currentItem) {

			var groupTreeitem = false;

			if (currentItem.isExpanded()) {
				groupTreeitem = currentItem;
			}
			else {
				groupTreeitem = currentItem.groupTreeitem;
			}

			if (groupTreeitem) {
				groupTreeitem.domNode.setAttribute('aria-expanded', false);
				this.updateVisibleTreeitems();
				this.setFocusToItem(groupTreeitem);
				il.UI.tree.toggleNodeState(groupTreeitem.domNode.id,1);
			}
		};

		TreeLinks.prototype.updateVisibleTreeitems = function () {

			this.firstTreeitem = this.treeitems[0];

			for (var i = 0; i < this.treeitems.length; i++) {
				var ti = this.treeitems[i];

				var parent = ti.domNode.parentNode;

				ti.isVisible = true;

				while (parent && (parent !== this.domNode)) {

					if (parent.getAttribute('aria-expanded') == 'false') {
						ti.isVisible = false;
					}
					parent = parent.parentNode;
				}

				if (ti.isVisible) {
					this.lastTreeitem = ti;
				}
			}

		};

		TreeLinks.prototype.setFocusByFirstCharacter = function (currentItem, char) {
			var start, index, char = char.toLowerCase();

			// Get start index for search based on position of currentItem
			start = this.treeitems.indexOf(currentItem) + 1;
			if (start === this.treeitems.length) {
				start = 0;
			}

			// Check remaining slots in the menu
			index = this.getIndexFirstChars(start, char);

			// If not found in remaining slots, check from beginning
			if (index === -1) {
				index = this.getIndexFirstChars(0, char);
			}

			// If match was found...
			if (index > -1) {
				this.setFocusToItem(this.treeitems[index]);
			}
		};

		TreeLinks.prototype.getIndexFirstChars = function (startIndex, char) {
			for (var i = startIndex; i < this.firstChars.length; i++) {
				if (this.treeitems[i].isVisible) {
					if (char === this.firstChars[i]) {
						return i;
					}
				}
			}
			return -1;
		};

		/**
		 * @private
		 */
		var TreeitemLink = function (node, treeObj, group) {

			// Check whether node is a DOM element
			if (typeof node !== 'object') {
				return;
			}

			node.tabIndex = -1;
			this.tree = treeObj;
			this.groupTreeitem = group;
			this.domNode = node;
			this.label = node.textContent.trim();
			this.stopDefaultClick = false;

			if (node.getAttribute('aria-label')) {
				this.label = node.getAttribute('aria-label').trim();
			}

			this.isExpandable = false;
			this.isVisible = false;
			this.inGroup = false;

			if (group) {
				this.inGroup = true;
			}

			var elem = node.firstElementChild;

			while (elem) {

				if (elem.tagName.toLowerCase() == 'ul') {
					elem.setAttribute('role', 'group');
					this.isExpandable = true;
					break;
				}

				elem = elem.nextElementSibling;
			}


			if(node.getAttribute("data-async_loaded") !== null){
				this.isExpandable = true;
			}

			this.keyCode = Object.freeze({
				RETURN: 13,
				SPACE: 32,
				PAGEUP: 33,
				PAGEDOWN: 34,
				END: 35,
				HOME: 36,
				LEFT: 37,
				UP: 38,
				RIGHT: 39,
				DOWN: 40
			});
		};

		TreeitemLink.prototype.init = function () {
			this.domNode.tabIndex = -1;

			if (!this.domNode.getAttribute('role')) {
				this.domNode.setAttribute('role', 'treeitem');
			}

			this.domNode.addEventListener('keydown', this.handleKeydown.bind(this));
			this.domNode.addEventListener('click', this.handleClick.bind(this));
		};

		TreeitemLink.prototype.isExpanded = function () {
			if (this.isExpandable) {
				return this.domNode.getAttribute('aria-expanded') === 'true';
			}

			return false;

		};

		TreeitemLink.prototype.handleKeydown = function (event) {
			var tgt = event.currentTarget,
				flag = false,
				char = event.key,
				clickEvent;

			function isPrintableCharacter (str) {
				return str.length === 1 && str.match(/\S/);
			}

			function printableCharacter (item) {
				if (char == '*') {
					item.tree.expandAllSiblingItems(item);
					flag = true;
				}
				else {
					if (isPrintableCharacter(char)) {
						item.tree.setFocusByFirstCharacter(item, char);
						flag = true;
					}
				}
			}

			this.stopDefaultClick = false;

			if (event.altKey || event.ctrlKey || event.metaKey) {
				return;
			}

			if (event.shift) {
				if (event.keyCode == this.keyCode.SPACE || event.keyCode == this.keyCode.RETURN) {
					event.stopPropagation();
					this.stopDefaultClick = true;
				}
				else {
					if (isPrintableCharacter(char)) {
						printableCharacter(this);
					}
				}
			}
			else {
				switch (event.keyCode) {
					case this.keyCode.SPACE:
					case this.keyCode.RETURN:
						if (this.isExpandable) {
							if (this.isExpanded()) {
								this.tree.collapseTreeitem(this);
							}
							else {
								this.tree.expandTreeitem(this);
							}
							flag = true;
						}
						else {
							event.stopPropagation();
							this.stopDefaultClick = true;
						}
						break;

					case this.keyCode.UP:
						this.tree.setFocusToPreviousItem(this);
						flag = true;
						break;

					case this.keyCode.DOWN:
						this.tree.setFocusToNextItem(this);
						flag = true;
						break;

					case this.keyCode.RIGHT:
						if (this.isExpandable) {
							if (this.isExpanded()) {
								this.tree.setFocusToNextItem(this);
							}
							else {
								this.tree.expandTreeitem(this);
							}
						}
						flag = true;
						break;

					case this.keyCode.LEFT:
						if (this.isExpandable && this.isExpanded()) {
							this.tree.collapseTreeitem(this);
							flag = true;
						}
						else {
							if (this.inGroup) {
								this.tree.setFocusToParentItem(this);
								flag = true;
							}
						}
						break;

					case this.keyCode.HOME:
						this.tree.setFocusToFirstItem();
						flag = true;
						break;

					case this.keyCode.END:
						this.tree.setFocusToLastItem();
						flag = true;
						break;

					default:
						if (isPrintableCharacter(char)) {
							printableCharacter(this);
						}
						break;
				}
			}

			if (flag) {
				event.stopPropagation();
				event.preventDefault();
			}
		};

		TreeitemLink.prototype.handleClick = function (event) {
			if (event.target !== this.domNode
				&& event.target !== this.domNode.firstElementChild
				&& this.domNode.getAttribute("data-async_loaded") !== undefined) {
				return;
			}

			if (this.isExpandable) {
				if (this.isExpanded()) {
					this.tree.collapseTreeitem(this);
				}
				else {
					this.tree.expandTreeitem(this);
				}
			}
		};

		/**
		 * End of section concerning keyboard handling for wcag specs.
		 */
		return public_interface;
	})($);
})($, il.UI);