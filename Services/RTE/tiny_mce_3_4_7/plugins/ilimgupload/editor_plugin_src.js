/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

(function() {
	tinymce.PluginManager.requireLangPack('ilimgupload');

	tinymce.create('tinymce.plugins.ilImgUpload', {
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

            ed.addCommand('ilimgupload', function() {
            	var src = "", alt = "", width = "", height = "";
            	var elm = ed.selection.getNode();
            	if ((elm != null) && (elm.nodeName.toLowerCase() == 'img'))
				{
					src = elm.getAttribute('src') ? elm.getAttribute('src') : "";
					alt = elm.getAttribute('alt') ? elm.getAttribute('alt') : "";
					width = elm.getAttribute('width') ? elm.getAttribute('width') : "";
					height = elm.getAttribute('height') ? elm.getAttribute('height') : "";
				} 
            	
            	var parameters = "?client_id=" + client_id;
				if (src.length > 0)
				{
					parameters += "&update=1";
				}
				parameters += "&obj_id=" + obj_id;
				parameters += "&obj_type=" + obj_type;
				parameters += "&session_id=" + session_id;

				ed.windowManager.open({
					file : url + '/imgupload.php' + parameters,
					width : 600,
					height : 400,
					ui : true
				}, {
					plugin_url : url, // Plugin absolute URL
					src : src,
					alt : alt,
					width : width,
					height : height
				});
			});

            // Register example button
			ed.addButton('ilimgupload', {
				title : 'ilimgupload.upload_image',
				cmd : 'ilimgupload',
				image : url + '/images/img_upload.png'
			});

            ed.onNodeChange.add(function(ed, cm, n, co) {
				cm.setActive('ilimgupload', n.nodeName == 'IMG' && !n.name);
			});
		},

		getInfo : function() {
			return {
				longname : 'ilImgUpload Plugin',
				author : 'Databay AG',
				authorurl : 'http://www.databay.de',
				infourl : 'http://www.databay.de',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('ilimgupload', tinymce.plugins.ilImgUpload);
})();