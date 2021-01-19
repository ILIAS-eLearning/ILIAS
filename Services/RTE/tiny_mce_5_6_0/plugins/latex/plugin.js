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
        init: function(ed, url) {
            var t = this;
            t.editor = ed;
            var _api = false;
            var host = tinymce.baseURI.host;
            var initHandler = function() {
                var selection = tinymce.activeEditor.selection;
                var latex_code = $('iframe').contents().find('#latex_code');

                var elm = tinymce.activeEditor.selection.getNode();
                // Get the selected contents as text and place it in the input
                var value = latex_code.val();

                if (elm != null) {
                    var id = ("getAttribute" in elm) ? elm.getAttribute("class") : '';
                    if (id == "latex") {
                        var text = "";
                        for (i = 0; i < elm.childNodes.length; i++) {
                            text = text + elm.childNodes[i].data;
                        }
                        if (text != 'undefined') {
                            value = text;
                        }
                    } else {
                        value = selection.getContent({ format: 'text' });
                    }
                } else {
                    value = selection.getContent({ format: 'text' });
                }
                latex_code.val(value);
                //onLatexCodeChanged.call($("#latex_code"));
            };


            // Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('mceExample');
            function mceLatex() {
                _api = ed.windowManager.openUrl({
                    title: tinymce.translate('latex.desc'),
                    url: url + '/latex.php',
                    width: 600,
                    height: 350,
                    buttons: [{
                        type: 'cancel',
                        name: 'cancel',
                        text: 'Cancel'
                    }, {
                        type: 'custom',
                        name: 'insert',
                        text: 'insert',
                        primary: true
                    }],
                    onAction: onInsertHandler,
                    onSubmit: onInsertHandler,

                });
            };

            var onInsertHandler = function(api) {
                var editor = tinymce.activeEditor;
                var latex_code = $('iframe').contents().find('#latex_code').val();
                var elm = tinymce.activeEditor.selection.getNode();
                if (latex_code.length > 0) {
                    if (elm == null) {
                        editor.execCommand("mceInsertContent", false, '<span class="latex">' + latex_code + '</span> ');
                    } else {
                        var id = elm.getAttribute("class");
                        if (id == "latex") {
                            elm.innerHTML = "";
                            editor.execCommand("mceRemoveNode", false, elm);
                            editor.execCommand("mceInsertContent", false, '<span class="latex">' + latex_code + '</span> ');
                        } else {
                            editor.execCommand("mceInsertContent", false, '<span class="latex">' + latex_code + '</span> ');
                        }
                    }
                }
                api.close();

            };
            ed.on('init', initHandler);
            //Register Icon
            var icon_path = "<img src='" + url + "/images/latex.gif'>";
            ed.ui.registry.addIcon('latex', icon_path);

            //Register Button
            ed.ui.registry.addButton('latex', {
                tooltip: tinymce.translate('latex.desc'),
                onAction: mceLatex,
                icon: 'latex'
            });
            var getLatexValue = function() {
                var selection = tinymce.activeEditor.selection;

                var elm = selection.getNode();
                // Get the selected contents as text and place it in the input
                var value = "";

                if (elm != null) {
                    var id = ("getAttribute" in elm) ? elm.getAttribute("class") : '';
                    if (id == "latex") {
                        var text = "";
                        for (i = 0; i < elm.childNodes.length; i++) {
                            text = text + elm.childNodes[i].data;
                        }
                        if (text != 'undefined') {
                            value = text;
                        }
                    } else {
                        value = selection.getContent({ format: 'text' });
                    }
                } else {
                    value = selection.getContent({ format: 'text' });
                }
                return value;
            };
            ed.addCommand('getInitialLatexContents', function(ui, values) {
                if (_api !== false) {
                    //send message
                    _api.sendMessage({
                        'content': getLatexValue(),
                        'labels': {
                            latex_code: tinymce.translate('latex.latex_code'),
                            preview: tinymce.translate('latex.preview')
                        }
                    }, host);
                }

            });


            var onInsertPasteLatex = function(api) {
                var data = api.getData();

                const latex = data['pastelatex'].trim();
                ed.insertContent(latex);
                api.close();
            }

            function pasteMCELatex(ui, v) {
                if (ui) {
                    if ((ed.getParam('paste_use_dialog', true)) || (!tinymce.isIE)) {
                        ed.windowManager.open({
                            title: tinymce.translate('latex.paste_desc'),
                            body: {
                                type: 'panel',
                                items: [{
                                    type: 'textarea',
                                    name: 'pastelatex',
                                    label: tinymce.translate('latex.paste_title')
                                }]
                            },
                            buttons: [{
                                type: 'submit',
                                text: 'Insert'
                            }, {
                                type: 'cancel',
                                text: 'Cancel'
                            }],
                            size: 'large',
                            onChange: null,
                            onSubmit: onInsertPasteLatex
                        });
                    } else
                        t._insertLatex(clipboardData.getData("Text"), true);
                } else
                    t._insertLatex(v.html, v.linebreaks);
            };

            // Register example button
            //Register Icon
            var paste_icon_path = "<img src='" + url + "/images/pastelatex.gif'>";
            ed.ui.registry.addIcon('pastelatex', paste_icon_path)

            ed.ui.registry.addButton('pastelatex', {
                tooltip: tinymce.translate('latex.paste_desc'),
                onAction: pasteMCELatex,
                icon: 'pastelatex',
                //ui : true
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
        createControl: function(n, cm) {
            return null;
        },

        /**
         * Returns information about the plugin as a name/value array.
         * The current keys are longname, author, authorurl, infourl and version.
         *
         * @return {Object} Name/value array containing information about the plugin.
         */
        getInfo: function() {
            return {
                longname: 'LaTeX Plugin',
                author: 'Helmut Schottm&uuml;ller',
                authorurl: 'http://www.aurealis.de',
                infourl: 'http://www.aurealis.de',
                version: "1.1"
            };
        },

        _insertLatex: function(content) {
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