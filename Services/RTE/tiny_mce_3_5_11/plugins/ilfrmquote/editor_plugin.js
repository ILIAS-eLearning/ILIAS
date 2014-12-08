/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

(function() {
	tinymce.PluginManager.requireLangPack('ilfrmquote');

	tinymce.create('tinymce.plugins.ilFrmQuote', {
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

			ed.onBeforeSetContent.add(function(ed, o) {
				o.content = t['_ilfrmquote2html'](ed, o.content);
			});

			ed.onPostProcess.add(function(ed, o) {
				if (o.set)
					o.content = t['_ilfrmquote2html'](ed, o.content);

				if (o.get)
					o.content = t['_html2ilfrmquote'](ed, o.content);
			});

			// Register button
			ed.addButton('ilFrmQuoteAjaxCall', {
				title : ed.getLang('ilfrmquote.quote'),
				cmd : 'ilFrmQuoteAjaxCall',
				image : url + '/images/quote.gif'
			});

			ed.addCommand('ilFrmQuoteAjaxCall', function() {
				if (ilFrmQuoteAjaxHandler) {
					new ilFrmQuoteAjaxHandler(t, ed);
				}
			});
		},

		getInfo : function() {
			return {
				longname : 'ilFrmQuote Plugin',
				author : 'Databay AG',
				authorurl : 'http://www.databay.de',
				infourl : 'http://www.databay.de',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		},

		// Private methods

		// HTML -> BBCode in PunBB dialect
		_html2ilfrmquote : function(ed, s) {
			s = tinymce.trim(s);

			function rep(re, str) {
				s = s.replace(re, str);
			};

			var startZ = this.substr_count(s, "<blockquote");
			var endZ = this.substr_count(s, "</blockquote>");

			if(startZ > endZ) {
				var diff = startZ - endZ;
				for(var i = 0; i < diff; i++) {
					s += "</blockquote>";
				}
			}
			else if(startZ < endZ) {
				var diff = endZ - startZ;
				for(var i = 0; i < diff; i++) {
					s = "<blockquote class=\"ilForumQuote\">" + s;
				}
			}
			rep(/<blockquote[\s]*?class="ilForumQuote"[\s]*?>[\s]*?<div[\s]*?class="ilForumQuoteHead"[\s]*?>[\s\S]*?\(([\s\S]*?)\)<\/div>/gi, "[quote=\"$1\"]");
			rep(/<blockquote(.*?)class="ilForumQuote"(.*?)>/gi, "[quote]");
			rep(/<\/blockquote>/gi, "[/quote]");
			return s;
		},

		_ilfrmquote2html : function(ed, s) {
			s = tinymce.trim(s);

			function rep(re, str) {
				s = s.replace(re, str);
			};

			var startZ = this.substr_count(s, "[quote");
			var endZ = this.substr_count(s, "[/quote]");

			if(startZ > endZ) {
				var diff = startZ - endZ;
				for(var i = 0; i < diff; i++) {
					s += "[/quote]";
				}
			}
			else if(startZ < endZ) {
				var diff = endZ - startZ;
				for(var i = 0; i < diff; i++) {
					s = "[quote]" + s;
				}
			}

			rep(/\[quote="(.*?)"\]/gi, "<blockquote class=\"ilForumQuote\"><div class=\"ilForumQuoteHead\">" + ed.translate('ilfrmquote.quote') + " ($1)</div>");
			rep(/\[quote]/gi, "<blockquote class=\"ilForumQuote\">");
			rep(/\[\/quote\]/gi, "</blockquote>");

			return s;
		},

		substr_count : function(haystack, needle, offset, length) {
			var pos = 0, cnt = 0;

			haystack += '';
			needle += '';
			if(isNaN(offset)) {offset = 0;}
			if(isNaN(length)) {length = 0;}
			offset--;

			while((offset = haystack.indexOf(needle, offset + 1)) != -1 ){
				if(length > 0 && (offset + needle.length) > length){
					return false;
				} else{
					cnt++;
				}
			}

			return cnt;
		}
	});

	// Register plugin
	tinymce.PluginManager.add('ilfrmquote', tinymce.plugins.ilFrmQuote);
})();