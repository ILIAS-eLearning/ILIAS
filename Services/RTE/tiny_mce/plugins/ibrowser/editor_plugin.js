/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/* Import plugin specific language pack */
tinyMCE.importPluginLanguagePack('ibrowser', 'en,de'); // <- Add a comma separated list of all supported languages

// Singleton class
var TinyMCE_ibrowserPlugin = {
	/**
	 * Returns information about the plugin as a name/value array.
	 * The current keys are longname, author, authorurl, infourl and version.
	 *
	 * @returns Name/value array containing information about the plugin.
	 * @type Array 
	 */
	getInfo : function() {
		return {
			longname : 'Image browser plugin',
			author : 'Helmut Schottmueller',
			authorurl : 'http://www.ilias.de',
			infourl : 'http://www.ilias.de',
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
		// inst.addShortcut('ctrl', 't', 'lang_ibrowser_desc', 'mceTemplate');
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
		var theme = tinyMCE.getParam("theme");
		
		switch (cn) {
			case "ibrowser":
				return tinyMCE.getButtonHTML(cn, 'lang_ibrowser_desc', "{$pluginurl}/images/ibrowser.gif", 'mceBrowseImage', true);
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
	execCommand : function(editor_id, element, command, user_interface, value) 
	{
		// Handle commands
		switch (command) {
			case "mceBrowseImage":
				if (obj_id.length == 0)
				{
					var message = tinyMCE.getLang('lang_ibrowser_error_no_obj_id');
					alert(message);
					return true;
				}
				if (user_interface) {
				}
				else 
				{
					// Call from a popup, e.g. contextmenu
					//alert("execCommand: mceBrowseImage gets called from popup.");
				}
				// Open a popup window and send in some custom data in a window argument
				var src = "", alt = "", border = "", hspace = "", vspace = "", width = "", height = "", align = "";
				if (tinyMCE.selectedElement != null && tinyMCE.selectedElement.nodeName.toLowerCase() == "img")
					tinyMCE.imgElement = tinyMCE.selectedElement;
	
				if (tinyMCE.imgElement) 
				{
					src = tinyMCE.imgElement.getAttribute('src') ? tinyMCE.imgElement.getAttribute('src') : "";
					alt = tinyMCE.imgElement.getAttribute('alt') ? tinyMCE.imgElement.getAttribute('alt') : "";
					border = tinyMCE.imgElement.getAttribute('border') ? tinyMCE.imgElement.getAttribute('border') : "";
					hspace = tinyMCE.imgElement.getAttribute('hspace') ? tinyMCE.imgElement.getAttribute('hspace') : "";
					vspace = tinyMCE.imgElement.getAttribute('vspace') ? tinyMCE.imgElement.getAttribute('vspace') : "";
					width = tinyMCE.imgElement.getAttribute('width') ? tinyMCE.imgElement.getAttribute('width') : "";
					height = tinyMCE.imgElement.getAttribute('height') ? tinyMCE.imgElement.getAttribute('height') : "";
					align = tinyMCE.imgElement.getAttribute('align') ? tinyMCE.imgElement.getAttribute('align') : "";
				} 

				var template = new Array();
				var parameters = "?client_id=" + client_id;
				if (src.length > 0)
				{
					parameters += "&update=1";
				}
				parameters += "&obj_id=" + obj_id;
				parameters += "&obj_type=" + obj_type;
				parameters += "&session_id=" + session_id;
				template['file'] = '../../plugins/ibrowser/imagemanager.php' + parameters; // Relative to theme location
				template['width'] = 480;
				template['height'] = 600;
	
				tinyMCE.openWindow(template, {editor_id : editor_id, src : src, alt : alt, border : border, hspace : hspace, vspace : vspace, width : width, height : height, align : align});
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
		// Select template button if parent node is a strong or b
		if (node.parentNode.nodeName == "IMG") 
		{
			tinyMCE.switchClass(editor_id + '_template', 'mceButtonSelected');
			return true;
		}

		// Deselect template button
		tinyMCE.switchClass(editor_id + '_template', 'mceButtonNormal');
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
		// top.status = "template plugin event: " + e.type;

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
				// alert("[FROM] Value HTML string: " + content);

				// Do custom cleanup code here

				break;

			case "insert_to_editor":
				// alert("[TO] Value HTML string: " + content);

				// Do custom cleanup code here

				break;

			case "get_from_editor_dom":
				// alert("[FROM] Value DOM Element " + content.innerHTML);

				// Do custom cleanup code here

				break;

			case "insert_to_editor_dom":
				// alert("[TO] Value DOM Element: " + content.innerHTML);

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
tinyMCE.addPlugin("ibrowser", TinyMCE_ibrowserPlugin);