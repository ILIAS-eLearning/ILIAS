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
	/* Import plugin specific language pack */
	tinymce.PluginManager.requireLangPack('ibrowser');

	// Singleton class
	tinymce.create('tinymce.plugins.iBrowserPlugin', {
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
				version : "1.1"
			};
		},

		init : function(ed, url) {
			// Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('mceExample');
			ed.addCommand('mceBrowseImage', function() {
				if (obj_id.length == 0)
				{
					var message = ed.getLang('ibrowser.error_no_obj_id');
					alert(message);
					return true;
				}

				var src = "", alt = "", border = "", hspace = "", vspace = "", width = "", height = "", align = "";
				var elm = ed.selection.getNode();
				if ((elm != null) && (elm.nodeName.toLowerCase() == 'img'))
				{
					src = elm.getAttribute('src') ? elm.getAttribute('src') : "";
					alt = elm.getAttribute('alt') ? elm.getAttribute('alt') : "";
					border = elm.getAttribute('border') ? elm.getAttribute('border') : "";
					hspace = elm.getAttribute('hspace') ? elm.getAttribute('hspace') : "";
					vspace = elm.getAttribute('vspace') ? elm.getAttribute('vspace') : "";
					width = elm.getAttribute('width') ? elm.getAttribute('width') : "";
					height = elm.getAttribute('height') ? elm.getAttribute('height') : "";
					align = elm.getAttribute('align') ? elm.getAttribute('align') : "";
				} 

				var template = new Array();
				var parameters = new String();
				parameters += "?obj_id=" + obj_id;
				parameters += "&obj_type=" + obj_type;
				if (src.length > 0) {
					parameters += "&update=1";
				}

				ed.windowManager.open({
					file : url + '/imagemanager.php' + parameters,
					width : 480,
					height : 600,
					ui : true
				}, {
					plugin_url : url, // Plugin absolute URL
					src : src,
					alt : alt,
					border : border,
					hspace : hspace,
					vspace : vspace,
					width : width,
					height : height,
					align : align
				});
			});

			// Register example button
			ed.addButton('ibrowser', {
				title : ed.getLang('ibrowser.desc'),
				cmd : 'mceBrowseImage',
				image : url + '/images/ibrowser.gif'
			});

			ed.onNodeChange.add(function(ed, cm, n, co) {
				cm.setActive('ibrowser', n.nodeName == 'IMG' && !n.name);
			});
		}
		
	});

	// Register plugin
	tinymce.PluginManager.add('ibrowser', tinymce.plugins.iBrowserPlugin);
})();