/**
 * $RCSfile$
 * $Revision$
 * $Date$
 *
 * @author Moxiecode
 * @copyright Copyright &copy; 2004-2006, Moxiecode Systems AB, All rights reserved.
 */

/* Import plugin specific language pack */
tinyMCE.importPluginLanguagePack('latex', 'en,de');

/****
 * Steps for creating a plugin from this template:
 *
 * 1. Change all "template" to the name of your plugin.
 * 2. Remove all the callbacks in this file that you don't need.
 * 3. Remove the popup.htm file if you don't need any popups.
 * 4. Add your custom logic to the callbacks you needed.
 * 5. Write documentation in a readme.txt file on how to use the plugin.
 * 6. Upload it under the "Plugins" section at sourceforge.
 *
 ****/

// Singleton class
var TinyMCE_LaTeXPlugin = {
	/**
	 * Returns information about the plugin as a name/value array.
	 * The current keys are longname, author, authorurl, infourl and version.
	 *
	 * @returns Name/value array containing information about the plugin.
	 * @type Array 
	 */
	getInfo : function() {
		return {
			longname : 'LaTeX plugin',
			author : 'Helmut Schottmüller',
			authorurl : 'http://www.nasbrill-soft.de',
			infourl : 'http://www.nasbrill-soft.de/docs/template.html',
			version : "1.0"
		};
	},

	/**
	 * Gets executed when a TinyMCE editor instance is initialized.
	 *
	 * @param {TinyMCE_Control} Initialized TinyMCE editor control instance. 
	 */
	initInstance : function(inst) {
		// Register custom keyboard shortcut
		inst.addShortcut('ctrl', 'l', 'lang_latex_desc', 'mceLaTeX');
	},

	/**
	 * Returns the HTML code for a specific control or empty string if this plugin doesn't have that control.
	 * A control can be a button, select list or any other HTML item to present in the TinyMCE user interface.
	 * The variable {$editor_id} will be replaced with the current editor instance id and {$pluginurl} will be replaced
	 * with the URL of the plugin. Language variables such as {$lang_somekey} will also be replaced with contents from
	 * the language packs.
	 *
	 * @param {string} cn Editor control/button name to get HTML for.
	 * @return HTML code for a specific control or empty string.
	 * @type string
	 */
	getControlHTML : function(cn) {
		switch (cn) {
			case "latex":
				return tinyMCE.getButtonHTML(cn, 'lang_latex_desc', '{$pluginurl}/images/latex.gif', 'mceLaTeX', true);
		}

		return "";
	},

	/**
	 * Executes a specific command, this function handles plugin commands.
	 *
	 * @param {string} editor_id TinyMCE editor instance id that issued the command.
	 * @param {HTMLElement} element Body or root element for the editor instance.
	 * @param {string} command Command name to be executed.
	 * @param {string} user_interface True/false if a user interface should be presented.
	 * @param {mixed} value Custom value argument, can be anything.
	 * @return true/false if the command was executed by this plugin or not.
	 * @type
	 */
	execCommand : function(editor_id, element, command, user_interface, value) {
		function _removeElement(element_name)
		{
			element_name = element_name.toLowerCase();
			elm = tinyMCE.getParentElement(tinyMCE.getInstanceById(editor_id).getFocusElement(), element_name);
			if(elm && elm.nodeName == element_name.toUpperCase())
			{
				tinyMCE.execCommand('mceBeginUndoLevel');
				tinyMCE.execCommand('mceRemoveNode', false, elm);
				tinyMCE.triggerNodeChange();
				tinyMCE.execCommand('mceEndUndoLevel');
			}
		}
		
		function _insertElement(element_name)
		{
			element_name = element_name.toLowerCase();
			var selection = tinyMCE.getInstanceById(editor_id).selection;
			//var b = selection.getBookmark();
			var selected = selection.getSelectedHTML();
			if ((selected.indexOf('<span class="latex">')) == -1)
			{
				if (selected.length > 0)
				{
					tinyMCE.execCommand('mceBeginUndoLevel');
					tinyMCE.execCommand('mceInsertContent', false, '<span class="latex">'+selected+'</span>');
					tinyMCE.triggerNodeChange();
					tinyMCE.execCommand('mceEndUndoLevel');
				}
			}
			//selection.moveToBookmark(b);
		}
		
		// Handle commands
		switch (command) {
			// Remember to have the "mce" prefix for commands so they don't intersect with built in ones in the browser.
			case "mceLaTeX":
				var element_name = "latex";
				var elm = tinyMCE.getParentElement(tinyMCE.getInstanceById(editor_id).getFocusElement(), element_name);
				if (elm != null && elm.nodeName == element_name.toUpperCase()) 
				{
					_removeElement(element_name);
				}
				else
				{
					_insertElement(element_name);
				}
				return true;
		}

		// Pass to next handler in chain
		return false;
	},

	/**
	 * Gets called ones the cursor/selection in a TinyMCE instance changes. This is useful to enable/disable
	 * button controls depending on where the user are and what they have selected. This method gets executed
	 * alot and should be as performance tuned as possible.
	 *
	 * @param {string} editor_id TinyMCE editor instance id that was changed.
	 * @param {HTMLNode} node Current node location, where the cursor is in the DOM tree.
	 * @param {int} undo_index The current undo index, if this is -1 custom undo/redo is disabled.
	 * @param {int} undo_levels The current undo levels, if this is -1 custom undo/redo is disabled.
	 * @param {boolean} visual_aid Is visual aids enabled/disabled ex: dotted lines on tables.
	 * @param {boolean} any_selection Is there any selection at all or is there only a cursor.
	 */
	handleNodeChange : function(editor_id, node, undo_index, undo_levels, visual_aid, any_selection) {
		// Deselect template button
		tinyMCE.switchClass(editor_id + '_template', 'mceButtonNormal');
		if (node == null) return;
		if (!any_selection) {
			// Disable the buttons
			tinyMCE.switchClass(editor_id + '_latex', 'mceButtonDisabled');
		} else {
			// A selection means the buttons should be active.
			tinyMCE.switchClass(editor_id + '_latex', 'mceButtonNormal');
		}
		switch (node.nodeName) {
			case "LATEX":
				tinyMCE.switchClass(editor_id + '_latex', 'mceButtonSelected');
				return true;
		}
		return true;
	},

	/**
	 * Gets called when a TinyMCE editor instance gets filled with content on startup.
	 *
	 * @param {string} editor_id TinyMCE editor instance id that was filled with content.
	 * @param {HTMLElement} body HTML body element of editor instance.
	 * @param {HTMLDocument} doc HTML document instance.
	 */
	setupContent : function(editor_id, body, doc) {
	},

	/**
	 * Gets called when the contents of a TinyMCE area is modified, in other words when a undo level is
	 * added.
	 *
	 * @param {TinyMCE_Control} inst TinyMCE editor area control instance that got modified.
	 */
	onChange : function(inst) {
	},

	/**
	 * Gets called when TinyMCE handles events such as keydown, mousedown etc. TinyMCE
	 * doesn't listen on all types of events so custom event handling may be required for
	 * some purposes.
	 *
	 * @param {Event} e HTML editor event reference.
	 * @return true - pass to next handler in chain, false - stop chain execution
	 * @type boolean
	 */
	handleEvent : function(e) {
		// Display event type in statusbar
		//top.status = "latex plugin event: " + e.type;

		return true; // Pass to next handler
	},

	/**
	 * Gets called when HTML contents is inserted or retrived from a TinyMCE editor instance.
	 * The type parameter contains what type of event that was performed and what format the content is in.
	 * Possible valuses for type is get_from_editor, insert_to_editor, get_from_editor_dom, insert_to_editor_dom.
	 *
	 * @param {string} type Cleanup event type.
	 * @param {mixed} content Editor contents that gets inserted/extracted can be a string or DOM element.
	 * @param {TinyMCE_Control} inst TinyMCE editor instance control that performes the cleanup.
	 * @return New content or the input content depending on action.
	 * @type string
	 */
	cleanup : function(type, content, inst) {
		switch (type) {
			case "get_from_editor":
				//alert("[FROM] Value HTML string: " + content);

				// Do custom cleanup code here

				break;

			case "insert_to_editor":
				//alert("[TO] Value HTML string: " + content);

				// Do custom cleanup code here

				break;

			case "get_from_editor_dom":
				//alert("[FROM] Value DOM Element " + content.innerHTML);

				// Do custom cleanup code here

				break;

			case "insert_to_editor_dom":
				//alert("[TO] Value DOM Element: " + content.innerHTML);

				// Do custom cleanup code here

				break;
		}

		return content;
	},

	// Private plugin internal methods

	/**
	 * This is just a internal plugin method, prefix all internal methods with a _ character.
	 * The prefix is needed so they doesn't collide with future TinyMCE callback functions.
	 *
	 * @param {string} a Some arg1.
	 * @param {string} b Some arg2.
	 * @return Some return.
	 * @type string
	 */
	_someInternalFunction : function(a, b) {
		return 1;
	}
};

// Adds the plugin class to the list of available TinyMCE plugins
tinyMCE.addPlugin("latex", TinyMCE_LaTeXPlugin);