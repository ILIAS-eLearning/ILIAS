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

(function() {
	// Load plugin specific language pack
	tinymce.PluginManager.requireLangPack('latex');

	tinymce.create('tinymce.plugins.LatexPlugin', {
		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 * This call is done before the editor instance has finished it's initialization so use the onInit event
		 * of the editor instance to intercept that event.
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function(ed, url) {
			var t = this;
			t.editor = ed; 

			// Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('mceExample');
			ed.addCommand('mcelatex', function() {
				ed.windowManager.open({
					file : url + '/latex.php',
					width : 600,
					height : 300,
					inline : 1
				}, {
					plugin_url : url, // Plugin absolute URL
					some_custom_arg : 'custom arg' // Custom argument
				});
			});

			// Register example button
			ed.addButton('latex', {
				title : 'latex.desc',
				cmd : 'mcelatex',
				image : url + '/images/latex.gif'
			});

			ed.addCommand('mceLatexPaste', function(ui, v) {
				if (ui) {
					if ((ed.getParam('paste_use_dialog', true)) || (!tinymce.isIE)) {
						ed.windowManager.open({
							file : url + '/pastelatex.htm',
							width : 450,
							height : 400,
							inline : 1
						}, {
							plugin_url : url
						});
					} else
						t._insertLatex(clipboardData.getData("Text"), true);
				} else
					t._insertLatex(v.html, v.linebreaks);
			});

			// Register example button
			ed.addButton('pastelatex', {
				title : 'latex.paste_desc',
				cmd : 'mceLatexPaste',
				image : url + '/images/pastelatex.gif',
				ui : true
			});
		},

		/**
		 * Creates control instances based in the incomming name. This method is normally not
		 * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
		 * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
		 * method can be used to create those.
		 *
		 * @param {String} n Name of the control to create.
		 * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
		 * @return {tinymce.ui.Control} New control instance or null if no control was created.
		 */
		createControl : function(n, cm) {
			return null;
		},

		/**
		 * Returns information about the plugin as a name/value array.
		 * The current keys are longname, author, authorurl, infourl and version.
		 *
		 * @return {Object} Name/value array containing information about the plugin.
		 */
		getInfo : function() {
			return {
				longname : 'LaTeX Plugin',
				author : 'Helmut Schottm&uuml;ller',
				authorurl : 'http://www.aurealis.de',
				infourl : 'http://www.aurealis.de',
				version : "1.1"
			};
		},
		
		_insertLatex : function(content) { 
			if (content && content.length > 0) {
				content = content.replace(new RegExp('\\$([^\\$]*)?\\$', 'g'), "<span class=\"latex\">$1</span>");
				content = content.replace(new RegExp('\\\\\\[', 'gi'), "<span class=\"latex\">");
				content = content.replace(new RegExp('\\\\\\]', 'gi'), "</span>");
//				content = content.replace(new RegExp('\\\\\\\\', 'g'), "<br />");
				tinyMCE.execCommand("mceInsertRawHTML", false, content); 
			}
		}
		
	});

	// Register plugin
	tinymce.PluginManager.add('latex', tinymce.plugins.LatexPlugin);
})();