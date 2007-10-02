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
tinyMCE.importPluginLanguagePack('latex', 'en,tr,de,sv,zh_cn,cs,fa,fr_ca,fr,pl,pt_br,nl,he,nb,ru,ru_KOI8-R,ru_UTF-8,nn,cy,es,is,zh_tw,zh_tw_utf8,sk,da');

var TinyMCE_LatexPlugin = {
	getInfo : function() {
		return {
			longname : 'LaTeX Plugin',
			author : 'Helmut Schottmüller',
			authorurl : 'http://www.nasbrill-soft.de',
			infourl : 'http://www.nasbrill-soft.de',
			version : tinyMCE.majorVersion + "." + tinyMCE.minorVersion
		};
	},

	initInstance : function(inst) {
		inst.addShortcut('ctrl', 'k', 'lang_latex_desc', 'mcelatex');
	},

	getControlHTML : function(cn) {
		switch (cn) {
			case "latex":
				return tinyMCE.getButtonHTML(cn, 'lang_latex_desc', '{$pluginurl}/images/latex.gif', 'mcelatex');
		}

		return "";
	},

	execCommand : function(editor_id, element, command, user_interface, value) {
		switch (command) {
			case "mcelatex":
				var anySelection = false;
				var inst = tinyMCE.getInstanceById(editor_id);
				var focusElm = inst.getFocusElement();
				var selectedText = inst.selection.getSelectedText();

				//if (tinyMCE.selectedElement)
				//	anySelection = (tinyMCE.selectedElement.nodeName.toLowerCase() == "span") || (selectedText && selectedText.length > 0);

				//if (anySelection || (focusElm != null && focusElm.nodeName.toLowerCase() == "span")) {
					var template = new Array();

					template['file']   = '../../plugins/latex/latex.php';
					template['width']  = 600;
					template['height'] = 300;

					tinyMCE.openWindow(template, {editor_id : editor_id, inline : "yes", resizable : "yes"});
				//}

				return true;
		}

		return false;
	},

	handleNodeChange : function(editor_id, node, undo_index, undo_levels, visual_aid, any_selection) {
			tinyMCE.switchClass(editor_id + '_latex', 'mceButtonNormal');
			return true;
		/*if (node == null)
			return;

		do {
			if (node.nodeName.toLowerCase() == "span" && (tinyMCE.getAttrib(node, 'class') != "") && (tinyMCE.getAttrib(node, 'class') == "latex")) {
				tinyMCE.switchClass(editor_id + '_latex', 'mceButtonSelected');
				return true;
			}
		} while ((node = node.parentNode));

		if (any_selection) {
			tinyMCE.switchClass(editor_id + '_latex', 'mceButtonNormal');
			return true;
		}

		tinyMCE.switchClass(editor_id + '_latex', 'mceButtonDisabled');

		return true;*/
	}
};

tinyMCE.addPlugin("latex", TinyMCE_LatexPlugin);
