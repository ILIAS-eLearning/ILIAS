var ilCOPage =
{
	content_css: '',
	edit_status: false,
	insert_status: false,
	minwidth: 50,
	minheight: 20,
	current_td: "",
	edit_ghost: null,
	drag_contents: [],
	drag_targets: [],
	ghost_debugged: false,
	quick_insert_id: null,
	pc_id_str: '',
	pasting: false,
	response_class: "",
	tds: {},

	////
	//// Debug/Error Functions
	////

	switchDebugGhost: function() {
		//var tp = document.getElementById('tinytarget_parent');
		if (!this.ghost_debugged)
		{
			$("#tinytarget_ifr").parent().parent().parent().parent().addClass("ilNoDisplay");
			//tp.style.display = 'none';
			this.ghost_debugged = true;
		}
		else
		{
			//tp.style.display = '';
			$("#tinytarget_ifr").parent().parent().parent().parent().removeClass("ilNoDisplay");
			this.ghost_debugged = false;
		}
	},

	debugContent: function()
	{
		var content = tinyMCE.get('tinytarget').getContent();
		alert(content);
		alert(this.getContentForSaving());
	},

	// display error
	displayError: function(str)
	{
		// build error string
		var estr;
		/* estr = "Sorry, an error occured. Please copy the content of this window and report the error at:<br /> " +
		 "<a href='http://www.ilias.de/mantis' target='_blank'>http://www.ilias.de/mantis</a>." +
		 "<p><b>User Agent</b></p>" +
		 navigator.userAgent + */
		estr = "<p><b>Error</b></p>";
		estr = estr + ilCOPage.error_str;
		estr = estr + "<p><b>Content</b></p>";
		var content = tinyMCE.get('tinytarget').getContent();
		content = content.split("<").join("&lt;");
		content = content.split(">").join("&gt;");
		estr = estr + content;


		var epan = document.getElementById('error_panel_inner');
		if (!epan)
		{
			var ediv = document.createElement('div');
//			var mc = document.getElementById("il_CenterColumn");

			ediv.innerHTML = "<div style='background-color:#FFFFFF;' id='error_panel'>" +
				"<div style='padding:20px; width:800px; height: 350px; overflow:auto;' id='error_panel_inner'>" + estr + "</div></div>";
			ediv.className = "yui-skin-sam";
			$('body').append(ediv);
//			ediv = mc.appendChild(ediv);
			var error_panel = new YAHOO.widget.Panel("error_panel", {
				close: true,
				constraintoviewport:true
			});
			error_panel.render();
			error_panel.moveTo(20, 20);
			ilCOPage.error_panel = error_panel;
		}
		else
		{
			epan.innerHTML =
				estr;
			ilCOPage.error_panel.show();
		}
	},

	////
	//// Setters/getters
	////

	setContentCss: function (content_css)
	{
		this.content_css = content_css;
	},

	setEditStatus: function(status)
	{
		if (status)
		{
//			YAHOO.util.DragDropMgr.lock();
		}
		else
		{
//			YAHOO.util.DragDropMgr.unlock();
		}
		var elements = YAHOO.util.Dom.getElementsByClassName('il_droparea');

		for (k in elements)
		{
			elements[k].style.visibility = 'hidden';
		}
		var obj = document.getElementById('ilPageEditModeMenu');
		if (obj) obj.style.visibility = 'hidden';
		var obj = document.getElementById('ilPageEditActionBar');
		if (obj) obj.style.visibility = 'hidden';
		var obj = document.getElementById('ilPageEditLegend');
		if (obj) obj.style.visibility = 'hidden';
		elements = YAHOO.util.Dom.getElementsByClassName('ilc_page_cont_PageContainer');
		for (k in elements)
		{
			elements[k].style.backgroundColor = '#F0F0F0';
		}
		elements = YAHOO.util.Dom.getElementsByClassName('ilc_page_Page');
		for (k in elements)
		{
			elements[k].style.backgroundColor = '#F0F0F0';
		}

		this.edit_status = status;
	},

	getEditStatus: function()
	{
		return this.edit_status;
	},

	setInsertStatus: function(status)
	{
		if (status)
		{
			this.quick_insert_id = null;
		}
		this.insert_status = status;
	},

	getInsertStatus: function()
	{
		return this.insert_status;
	},


	////
	//// Text editor commands
	////

	cmdSave: function (switch_to)
	{
		$('#ilsaving').removeClass("ilNoDisplay");

		// table editing
		if (ilCOPage.current_td != "")
		{
			var ed = tinyMCE.get('tinytarget');
			this.autoResize(ed);
			this.setEditStatus(false);
			//ilFormSend("saveDataTable", ed_para, null, null);
			var pars = ilCOPage.tds;
			this.sendCmdRequest("saveDataTable", ed_para, null,
				pars,
				false, null, null);
			return;
		}

		if (this.getInsertStatus())
		{
//			ilFormSend("insertJS", ed_para, null, "saveonly");
			var content = this.getContentForSaving();
			var style_class = il.AdvancedSelectionList.getHiddenInput('style_selection');
			//this.copyInputToGhost(false);
			//this.removeTiny();
			// pc_id_str: ed_para,

			if (ed_para == "")
			{
				alert("Error: Calling insertJS without ed_para.");
				return;
			}

			this.sendCmdRequest("insertJS", ed_para, null,
				{ajaxform_content: content,
					ajaxform_char: style_class,
					insert_at_id: ed_para,
					quick_save: 1},
				true, {switch_to: switch_to}, this.quickInsertAjaxSuccess);
		}
		else
		{
			//ilFormSend("saveJS", ed_para, null, "saveonly");
			var content = this.getContentForSaving();
			var style_class = il.AdvancedSelectionList.getHiddenInput('style_selection');
			//this.copyInputToGhost(false);
			//this.removeTiny();

			if (this.pc_id_str == "")
			{
				alert("Error: Calling saveJS without pc_id_str.");
				return;
			}
			this.sendCmdRequest("saveJS", this.pc_id_str, null,
				{ajaxform_content: content,
					pc_id_str: this.pc_id_str,
					ajaxform_char: style_class,
					quick_save: 1},
				true, {switch_to: switch_to}, this.quickSavingAjaxSuccess);

		}
	},

	cmdSaveReturn: function (and_new)
	{
		$('#ilsaving').removeClass("ilNoDisplay");

		var ed = tinyMCE.get('tinytarget');
		this.autoResize(ed);
		this.setEditStatus(false);
		if (ilCOPage.current_td != "")
		{
			//ilFormSend("saveDataTable", ed_para, null, null);
			var pars = ilCOPage.tds;
			pars.save_return = 1;
			this.sendCmdRequest("saveDataTable", ed_para, null,
				pars,
				false, null, null);
		}
		else if (this.getInsertStatus() && !ilCOPage.quick_insert_id)
		{
			var content = this.getContentForSaving();;
			var style_class = il.AdvancedSelectionList.getHiddenInput('style_selection');

			if (ed_para == "")
			{
				alert("Error2: Calling insertJS without ed_para.");
				return;
			}

			this.sendCmdRequest("insertJS", ed_para, null,
				{ajaxform_content: content,
					pc_id_str: this.pc_id_str,
					insert_at_id: ed_para,
					ajaxform_char: style_class},
				true, {and_new: and_new}, this.saveReturnAjaxSuccess);
		}
		else
		{
			var content = this.getContentForSaving();
			var style_class = il.AdvancedSelectionList.getHiddenInput('style_selection');

			if (this.pc_id_str == "")
			{
				alert("Error2: Calling saveJS without pc_id_str.");
				return;
			}

			this.sendCmdRequest("saveJS", this.pc_id_str, null,
				{ajaxform_content: content,
					pc_id_str: this.pc_id_str,
					ajaxform_char: style_class},
				true, {and_new: and_new}, this.saveReturnAjaxSuccess);
		}
	},

	switchTo: function(pc_id)
	{
		this.cmdSave(pc_id);
	},

	cmdCancel: function ()
	{
		var ed = tinyMCE.get('tinytarget');
		this.autoResize(ed);
		this.setEditStatus(false);
		this.setInsertStatus(false);
		this.copyInputToGhost(false);
		this.removeTiny();
		hideToolbar();
		if (ilCOPage.current_td == "")
		{
			this.sendCmdRequest("cancel", ed_para, null, {},
				true, {}, this.pageReloadAjaxSuccess);
		}
		else
		{
			this.sendCmdRequest("saveDataTable", ed_para, null,
				{cancel_update: 1}, null, null);
		}

	},

	setCharacterClass: function(i)
	{
		switch (i.hid_val)
		{
			case "Quotation":
			case "Comment":
			case "Accent":
				this.cmdSpan(i.hid_val);
				break;

			case "Code":
				this.cmdCode();
				break;
		}
		return false;
	},

	cmdSpan: function (t)
	{
		var stype = {Strong: '0', Emph: '1', Important: '2', Comment: '3',
			Quotation: '4', Accent: '5'};

		var ed = tinyMCE.get('tinytarget');
		/*
		 var st_sel = ed.controlManager.get('styleselect');

		 // from tiny_mce_src-> renderMenu
		 if (st_sel.settings.onselect('style_' + stype[t]) !== false)
		 st_sel.select('style_' + stype[t]); // Must be runned after */

		tinymce.activeEditor.formatter.toggle(t);

		this.autoResize(ed);
	},

	cmdCode: function()
	{
		var ed = tinyMCE.get('tinytarget');

		tinymce.activeEditor.formatter.register('mycode', {
			inline : 'code'
		});
		ed.execCommand('mceToggleFormat', false, 'mycode');
		this.autoResize(ed);
	},

	cmdRemoveFormat: function()
	{
		var ed = tinyMCE.get('tinytarget');
		ed.focus();
		ed.execCommand('RemoveFormat', false);
		this.autoResize(ed);
	},

	cmdPasteWord: function()
	{
		var ed = tinyMCE.get('tinytarget');
		ed.focus();
		ed.execCommand('mcePasteWord');
	},

	cmdIntLink: function(b, e)
	{
		this.addBBCode(b, e);
	},

	getSelection: function() {
		var ed = tinyMCE.get('tinytarget'), r, rcopy;
		ed.focus();
		return ed.selection.getContent();
	},

	addBBCode: function(stag, etag, clearselection)
	{
		var ed = tinyMCE.get('tinytarget'), r, rcopy;
		ed.focus();
		if (ed.selection.getContent() == "")
		{
			rcopy = ed.selection.getRng(true).cloneRange();
			var nc = stag + ed.selection.getContent() + etag;
			ed.selection.setContent(nc);
			ed.focus();
			r =  ed.dom.createRng();
			if (rcopy.endContainer.nextSibling) // usual text node
			{
				if (rcopy.endContainer.nextSibling.nodeName != "P")
				{
					r.setEnd(rcopy.endContainer.nextSibling, stag.length);
					r.setStart(rcopy.startContainer.nextSibling, stag.length);
					ed.selection.setRng(r);
				}
				else
				{
					r.setStart(rcopy.endContainer.firstChild, stag.length);
					r.setEnd(rcopy.endContainer.firstChild, stag.length);
					ed.selection.setRng(r);
				}
			}
			else if (rcopy.endContainer.firstChild) // e.g. when being in an empty list node
			{
				r.setEnd(rcopy.endContainer.firstChild, stag.length);
				r.setStart(rcopy.startContainer.firstChild, stag.length);
				ed.selection.setRng(r);
			}
			ed.selection.setRng(r);
		}
		else
		{
			if (clearselection) {
				ed.selection.setContent(stag + etag);
			}
			else {
				ed.selection.setContent(stag + ed.selection.getContent() + etag);
			}
		}
		this.autoResize(ed);
	},

	cmdWikiLink: function()
	{
		this.addBBCode('[[', ']]');
	},

	cmdTex: function()
	{
		this.addBBCode('[tex]', '[/tex]');
	},

	cmdFn: function()
	{
		this.addBBCode('[fn]', '[/fn]');
	},

	cmdKeyword: function()
	{
		this.addBBCode('[kw]', '[/kw]');
	},

	cmdExtLink: function()
	{
		this.addBBCode('[xln url="http://"]', '[/xln]');
	},

	cmdAnc: function()
	{
		this.addBBCode('[anc name=""]', '[/anc]');
	},

	cmdBList: function()
	{
		var ed = tinyMCE.get('tinytarget');
		ed.focus();
		ed.execCommand('InsertUnorderedList', false);
		this.fixListClasses(true);
		this.autoResize(ed);
	},

	cmdNList: function()
	{
		var ed = tinyMCE.get('tinytarget');
		ed.focus();
		ed.execCommand('InsertOrderedList', false);
		this.fixListClasses(true);
		this.autoResize(ed);
	},

	cmdListIndent: function()
	{
		var ed = tinyMCE.get('tinytarget');
		ed.focus();
		ed.execCommand('Indent', false);
		this.fixListClasses(false);
		this.autoResize(ed);
	},

	cmdListOutdent: function()
	{
		var ed = tinyMCE.get('tinytarget');
		ed.focus();
		ed.execCommand('Outdent', false);
		this.fixListClasses(true);
		this.autoResize(ed);
	},

	setParagraphClass: function(i)
	{
		var ed = tinyMCE.activeEditor;
		ed.focus();
		var snode = ed.dom.getRoot();

		if (snode)
		{
			snode.className = "ilc_text_block_" + i['hid_val'];
			snode.style.position ='static';
		}
		this.autoResize(ed);
	},

	////
	//// Content modifier
	////

	/**
	 * Get content to be sent per ajax to server.
	 */
	getContentForSaving: function()
	{
		var ed = tinyMCE.get('tinytarget');
		var cl = ed.dom.getRoot().className;
		var c = ed.getContent();

		c = this.p2br(c);

		// add wrapping div with style class
		c = "<div id='" + this.pc_id_str + "' class='" + cl + "'>" + c + "</div>";

		return c;
	},

	// convert <p> tags to <br />
	p2br: function(c)
	{
		// remove <p> and \n
		c = c.split("<p>").join("");
		c = c.split("\n").join("");

		// convert </p> to <br />
		c = c.split("</p>").join("<br />");

		// remove trailing <br />
		if (c.substr(c.length - 6) == "<br />")
		{
			c = c.substr(0, c.length - 6);
		}

		return c;
	},


	/**
	 * This function converts all <br /> into corresponding paragraphs
	 * (server content comes with <br />, but tiny has all kind of issues
	 * in "<br>" mode (e.g. IE cannot handle lists). So we use the more
	 * reliable "<p>" mode of tiny.
	 */
	splitBR: function()
	{
		var snode;
		var ed = tinyMCE.activeEditor;
		var r = ed.dom.getRoot();

		// STEP 1: Handle all top level <br />

		// make copy of root
		var rcopy = r.cloneNode(true);

		// remove all childs of top level
		for (var k = r.childNodes.length - 1; k >= 0; k--)
		{
			r.removeChild(r.childNodes[k]);
		}

		// cp -> current P
		var cp = ed.dom.create('p', {}, '');
		var cp_content = false; // has current P any content?
		var cc, pc; // cc: currrent child (top level), pc: P child

		// walk through root copy and add content to emptied original root
		for (var k = 0; k < rcopy.childNodes.length; k++)
		{
			cc = rcopy.childNodes[k];

			// handle Ps on top level
			// main purpose: convert <p> ...<br />...</p> to <p>...</p><p>...</p>
			if (cc.nodeName == "P")
			{
				// is there a current P with content? -> add it to top level
				if (cp_content)
				{
					r.appendChild(cp);
					cp = ed.dom.create('p', {}, '');
					cp_content = false;
				}

				// split all BRs into separate Ps on top level
				for (var i = 0; i < cc.childNodes.length; i++)
				{
					pc = cc.childNodes[i];
					if (pc.nodeName == "BR")
					{
						// append the current p an create a new one
						r.appendChild(cp);
						cp = ed.dom.create('p', {}, '');
						cp_content = false;
					}
					else
					{
						// append the content to the current p
						cp.appendChild(pc.cloneNode(true));
						cp_content = true;
					}
				}

				// append current p and create a new one
				if (cp_content)
				{
					r.appendChild(cp);
					cp = ed.dom.create('p', {}, '');
					cp_content = false;
				}
			}
			else if (cc.nodeName == "UL" || cc.nodeName == "OL")
			{
				// UL and OL are simply appended to the root
				if (cp_content)
				{
					r.appendChild(cp);
					cp = ed.dom.create('p', {}, '');
					cp_content = false;
				}
				r.appendChild(rcopy.childNodes[k].cloneNode(true));
			}
			else
			{
				cp.appendChild(rcopy.childNodes[k].cloneNode(true));
				cp_content = true;
			}
		}
		if (cp_content)
		{
			r.appendChild(cp);
		}

		// STEP 2: Handle all non-top level <br />
		// this is the standard tiny br splitting (which fails in top level Ps)
		/*		tinymce.each(ed.dom.select('br').reverse(), function(b) {
		 try {
		 var snode = ed.dom.getParent(b, 'p,li');
		 ed.dom.split(snode, b);
		 } catch (ex) {
		 // IE can sometimes fire an unknown runtime error so we just ignore it
		 }
		 });*/
		ilCOPage.splitTopBr();


		// STEP 3: Clean up

		// remove brs (normally all should have been handled above)
		var c = ed.getContent();
		c = c.split("<br />").join("");
		c = c.split("\n").join("");
		ed.setContent(c);
	},

	// split all span classes that are direct "children of themselves"
	// fixes bug #13019
	splitSpans: function() {

		var k, ed = tinyMCE.activeEditor, s,
			classes = ['ilc_text_inline_Strong','ilc_text_inline_Emph', 'ilc_text_inline_Important',
				'ilc_text_inline_Comment', 'ilc_text_inline_Quotation', 'ilc_text_inline_Accent'];

		for (var i = 0; i < classes.length; i++) {

			s = ed.dom.select('span[class="' + classes[i] + '"] > span[class="' + classes[i] + '"]');
			for (k in s) {
				ed.dom.split(s[k].parentNode, s[k]);
			}
		}
	},

	/**
	 * This one ensures that the standard ILIAS list style classes
	 * are assigned to list elements
	 */
	fixListClasses: function(handle_inner_br)
	{
		var ed = tinyMCE.activeEditor;

		// return;

		ed.dom.addClass(tinyMCE.activeEditor.dom.select('ol'), 'ilc_list_o_NumberedList');
		ed.dom.addClass(tinyMCE.activeEditor.dom.select('ul'), 'ilc_list_u_BulletedList');
		ed.dom.addClass(tinyMCE.activeEditor.dom.select('li'), 'ilc_list_item_StandardListItem');

		if (handle_inner_br)
		{
			var rcopy = ed.selection.getRng(true);
			var target_pos = false;

			// get selection start p or li tag
			var st_cont = rcopy.startContainer.nodeName.toLowerCase();
			if (st_cont != "p" && st_cont != "li")
			{
				var par = rcopy.startContainer.parentNode;
				if (par.nodeName.toLowerCase() == "body")
				{
					// starting from something like a text node under body
					// not really a parent anymore, but ok to get the previous sibling from
					par = rcopy.startContainer;
				}
				else
				{
					// starting from a deeper node in text
					while (par.parentNode &&
						par.nodeName.toLowerCase() != "li" &&
						par.nodeName.toLowerCase() != "p" &&
						par.nodeName.toLowerCase() != "body")
					{
						par = par.parentNode;
						//console.log(par);
					}
				}
			}
			else
			{
				var par = rcopy.startContainer;
			}
			//console.log(par);


			// get previous sibling
			var ps = par.previousSibling;
			if (ps)
			{
				if (ps.nodeName.toLowerCase() == "p" ||
					ps.nodeName.toLowerCase() == "li")
				{
					target_pos = ps;
				}
				if (ps.nodeName.toLowerCase() == "ul")
				{
					if (ps.lastChild)
					{
						target_pos = ps.lastChild;
					}
				}
			}
			else
			{
				//console.log("case d");
				// set selection to beginning
				var r = ed.dom.getRoot();
				target_pos = r.childNodes[0];
			}
			if (this.splitTopBr())
			{
				//console.log("setting range");

				// set selection to start of first div
				if (target_pos)
				{
					var r =  ed.dom.createRng();
					r.setStart(target_pos, 0);
					r.setEnd(target_pos, 0);
					ed.selection.setRng(r);
				}
			}
		}
	},

	splitTopBr: function()
	{
		var changed = false;

		var ed = tinyMCE.activeEditor;
		ed.getContent(); // this line is imporant and seems to fix some things
		tinymce.each(ed.dom.select('br').reverse(), function(b) {

//console.log(b);
//return;

			try {
				var snode = ed.dom.getParent(b, 'p,li');
				if (snode.nodeName != "LI" &&
					snode.childNodes.length != 1)
				{
//				ed.dom.split(snode, b);

					function trim(node) {
						var i, children = node.childNodes;

						if (node.nodeType == 1 && node.getAttribute('_mce_type') == 'bookmark')
							return;

						for (i = children.length - 1; i >= 0; i--)
							trim(children[i]);

						if (node.nodeType != 9) {
							// Keep non whitespace text nodes
							if (node.nodeType == 3 && node.nodeValue.length > 0) {
								// If parent element isn't a block or there isn't any useful contents for example "<p>   </p>"
								if (!t.isBlock(node.parentNode) || tinymce.trim(node.nodeValue).length > 0)
									return;
							}

							if (node.nodeType == 1) {
								// If the only child is a bookmark then move it up
								children = node.childNodes;
								if (children.length == 1 && children[0] && children[0].nodeType == 1 && children[0].getAttribute('_mce_type') == 'bookmark')
									node.parentNode.insertBefore(children[0], node);

								// Keep non empty elements or img, hr etc
								if (children.length || /^(br|hr|input|img)$/i.test(node.nodeName))
									return;
							}

							t.remove(node);
						}

						return node;
					};


					var pe = snode;
					var e = b;
					if (pe && e) {
						var t = ed.dom, r = t.createRng(), bef, aft, pa;

						// Get before chunk
						r.setStart(pe.parentNode, t.nodeIndex(pe));
						r.setEnd(e.parentNode, t.nodeIndex(e));
						bef = r.extractContents();

						// Get after chunk
						r = t.createRng();
						r.setStart(e.parentNode, t.nodeIndex(e) + 1);
						r.setEnd(pe.parentNode, t.nodeIndex(pe) + 1);
						aft = r.extractContents();

						// Insert before chunk
						pa = pe.parentNode;
						pa.insertBefore(trim(bef), pe);
						//pa.insertBefore(bef, pe);

						// Insert after chunk
						pa.insertBefore(trim(aft), pe);
						//pa.insertBefore(aft, pe);
						t.remove(pe);

						//					return re || e;
						changed = true;
					}
				}

			} catch (ex) {
				// IE can sometimes fire an unknown runtime error so we just ignore it
			}
		});
		return changed;
	},

	// remove all divs (used after pasting)
	splitDivs: function()
	{
		// split all divs in divs
		var ed = tinyMCE.activeEditor;
		var divs = ed.dom.select('p > div');
		var k;
		for (k in divs)
		{
			ed.dom.split(divs[k].parentNode, divs[k]);
		}
	},

	////
	//// Tiny/text area/menu handling
	////

	prepareTinyForEditing: function(insert, switched)
	{
		var ed = tinyMCE.get('tinytarget');
		//tinyMCE.execCommand('mceAddControl', false, 'tinytarget');
		tinyMCE.execCommand('mceAddEditor', false, 'tinytarget');
//console.log("prepareTiny");
		if (!switched)
		{
			showToolbar('tinytarget');
		}

// todo tinynew
//		tinyifr = document.getElementById("tinytarget_parent");
//		tinyifr.style.position = "absolute";

		this.setEditStatus(true);
		this.setInsertStatus(insert);
		if (!insert)
		{
			this.focusTiny(false);
		}
		//this.autoScroll();
		if (ilCOPage.current_td != "")
		{
			this.copyInputToGhost(false);
		}
		else
		{
			this.copyInputToGhost(true);
		}
		this.synchInputRegion();
		this.updateMenuButtons();
	},

	focusTiny: function(delayed)
	{
		var timeout = 1;
		if (delayed)
		{
			timeout = 500;
		}

		setTimeout(function () {
			var ed = tinyMCE.get('tinytarget');
			if (ed)
			{
				var e = tinyMCE.DOM.get(ed.id + '_external');
				var r = ed.dom.getRoot();
				// div
				//	var fdiv = r.childNodes[0];
				// p
				var fc = r.childNodes[0];
				if (fc != null)
				{
					// set selection to start of first div
					// this does not seem to be necessary
					// with 4.0.12 (firefox, chrome, safari)
					/*					var rn = ed.dom.createRng();
					 rn.setStart(fc, 0);
					 rn.setEnd(fc, 0);
					 ed.selection.setRng(rn);*/
				}
				if (r.className != null)
				{
					var st = r.className.substring(15);
					il.AdvancedSelectionList.selectItem('style_selection', st);
				}

				ed.getWin().focus();
			}
		}, timeout);
	},

	removeTiny: function() {
		tinyMCE.execCommand('mceRemoveEditor', false, 'tinytarget');
//		tinyMCE.execCommand('mceRemoveControl', false, 'tinytarget');
		var tt = document.getElementById("tinytarget");
		tt.style.display = 'none';
	},

	// set frame size of editor
	setEditFrameSize: function(width, height)
	{
		var tinyifr = document.getElementById("tinytarget_ifr");
		var tinytd = document.getElementById("tinytarget_tbl");;
		tinyifr.style.width = width + "px";
		tinyifr.style.height = height + "px";
//		tinytd.style.width = width + "px";
//		tinytd.style.height = height + "px";

		$("#tinytarget_ifr").css("width", width + "px");
		$("#tinytarget_ifr").css("height", height + "px");

		this.ed_width = width;
		this.ed_height = height;
	},

	// copy input of tiny to ghost div in background
	copyInputToGhost: function(add_final_spacer)
	{
		if (add_final_spacer)
		{
//	console.trace();
		}
		var ed = tinyMCE.get('tinytarget');

		if (this.edit_ghost)
		{
			var pdiv = document.getElementById(this.edit_ghost);
			if (pdiv)
			{
				var cl = ed.dom.getRoot().className;
				var c = this.p2br(ed.getContent());
				if (ilCOPage.current_td == "")
				{
					var c = "<div style='position:static;' class='" + cl + "'>" + c + "</div>";
				}
				else
				{
					ilCOPage.tds[ilCOPage.current_td] =
						ilCOPage.getContentForSaving();
				}
				var e = c.substr(c.length - 6);
				var b = c.substr(c.length - 12, 6);
				if (e == "</div>" && add_final_spacer)
				{
					// ensure at least one more line of space
					if (b != "<br />") {
						c = c.substr(0, c.length - 6) + "<br />.</div>";
					} else {
						// this looks good under firefox. If this leads to problems on other
						// browsers, ".</div>" would be the alternative for this case (last new empty line)
						c = c.substr(0, c.length - 6) + "<br />.</div>";
					}

				}
				pdiv.innerHTML = c;
			}
		}
	},

	// synchs the size/position of the tiny to the space the ghost
	// object uses in the background
	synchInputRegion: function()
	{
		var back_el, dummy;

		if (this.current_td)
		{
			back_el = document.getElementById(this.edit_ghost);
			back_el = back_el.parentNode;
		}
		else
		{
			back_el = document.getElementById(this.edit_ghost);
		}

		if (!back_el) {
			return;
		}

		back_el.style.minHeight = ilCOPage.minheight + "px";
//		back_el.style.minWidth = ilCOPage.minwidth + "px";

		// alex, 30 Dec 2011, see bug :
		// for reasons I do not understand, the above does not
		// work for IE7, even if minWidth is implemented there.
		// so we do this "padding" trick which works for all browsers
		if ($(back_el).width() < ilCOPage.minwidth)
		{
			var new_pad = (ilCOPage.minwidth - $(back_el).width()) / 2;
			back_el.style.paddingLeft = new_pad + "px";
			back_el.style.paddingRight = new_pad + "px";
		}
		else
		{
			back_el.style.paddingLeft = "";
			back_el.style.paddingRight = "";
		}

		//tinyifr = document.getElementById("tinytarget_parent");
		tinyifr = document.getElementById("tinytarget_ifr");
		tinyifr = tinyifr.parentNode;
		$(tinyifr).css("position", "absolute");

		// make sure, background element does not go beyond page bottom
		back_el.style.display = '';
		back_el.style.overflow = 'auto';
		back_el.style.height = '';
		var back_reg = YAHOO.util.Region.getRegion(back_el);
		var cl_reg = YAHOO.util.Dom.getClientRegion();
		if (back_reg.y + back_reg.height + 20 > cl_reg.top + cl_reg.height)
		{
			back_el.style.overflow = 'hidden';
			back_el.style.height = (cl_reg.top + cl_reg.height - back_reg.y - 20) + "px";
			back_reg = YAHOO.util.Region.getRegion(back_el);
		}

		if (this.current_td)
		{
			YAHOO.util.Dom.setX(tinyifr, back_reg.x -2);
			YAHOO.util.Dom.setY(tinyifr, back_reg.y -2);
			this.setEditFrameSize(back_reg.width-2,
				back_reg.height);
		}
		else
		{
			if (ilCOPage.getInsertStatus())
			{
				YAHOO.util.Dom.setX(tinyifr, back_reg.x - 1);
				YAHOO.util.Dom.setY(tinyifr, back_reg.y);
				this.setEditFrameSize(back_reg.width + 1,
					back_reg.height);
			}
			else
			{
				YAHOO.util.Dom.setX(tinyifr, back_reg.x);
				YAHOO.util.Dom.setY(tinyifr, back_reg.y);
				this.setEditFrameSize(back_reg.width,
					back_reg.height);
			}
		}

		if (!this.current_td) {
			ilCOPage.autoScroll();
		}

		// force redraw for webkit based browsers (ILIAS chrome bug #0010871)
		// http://stackoverflow.com/questions/3485365/how-can-i-force-webkit-to-redraw-repaint-to-propagate-style-changes
		// no feature detection here since we are fixing a webkit bug and IE does not like this patch (starts flickering
		// on "short" pages)
		var isChrome = /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor);
		var isSafari = /Safari/.test(navigator.userAgent) && /Apple Computer/.test(navigator.vendor);
		if (isChrome || isSafari) {
			back_el.style.display='none';
			dummy = back_el.offsetHeight;
			back_el.style.display='';
		}
	},

	autoResize: function(ed) {
		ilCOPage.copyInputToGhost(true);
		ilCOPage.synchInputRegion();
	},

	// scrolls position of editor under editor menu
	autoScroll: function() {
		var tiny_reg, menu_reg, cl_reg, diff;

		//var tinyifr = document.getElementById("tinytarget_parent");
		var tinyifr = document.getElementById("tinytarget_ifr");
		var menu = document.getElementById('iltinymenu');
		var fc = document.getElementById('fixed_content');

		if (tinyifr && menu) {

			if ($(fc).css("position") == "static") {
				tiny_reg = YAHOO.util.Region.getRegion(tinyifr);
				menu_reg = YAHOO.util.Region.getRegion(menu);
				//console.log(tiny_reg);
				//console.log(menu_reg);
				cl_reg = YAHOO.util.Dom.getClientRegion();
				//console.log(cl_reg);
				//console.log(-20 + tiny_reg.y - (menu_reg.height + menu_reg.y - cl_reg.top));
				window.scrollTo(0, -20 + tiny_reg.y - (menu_reg.height + menu_reg.y - cl_reg.top));
			} else {
				diff = Math.floor($(menu).offset().top + $(menu).height()  + 20 - $(tinyifr).offset().top);
				if (diff > 1 || diff < -1) {
					$(fc).scrollTop($(fc).scrollTop() - diff);
				}
			}
		}
	},

	updateMenuButtons: function()
	{
		var ed = tinyMCE.get('tinytarget');
		// update buttons
		var cnode = ed.selection.getNode();
		while (cnode)
		{
			if (cnode.parentNode &&
				cnode.parentNode.nodeName.toLowerCase() == "body" &&
				cnode.nodeName.toLowerCase() == "div")
			{
				var st = cnode.className.substring(15);
				//var st_s = document.getElementById('style_selection');
				//if (st_s != null)
				//{
				il.AdvancedSelectionList.selectItem('style_selection', st);
				//}
			}

			cnode = cnode.parentNode;
		}
	},


	////
	//// Table editing
	////

	editTD: function(id)
	{
		editParagraph(id, 'td', false);
		//var ed = tinyMCE.get('tinytarget');
		//this.focusTiny();
	},

	editNextCell: function()
	{
		// check whether next cell exists
		var cdiv = this.current_td.split("_");
		var next = "cell_" + cdiv[1] + "_" + (parseInt(cdiv[2]) + 1);
		var nobj = document.getElementById("div_" + next);
		if (nobj == null)
		{
			var next = "cell_" + (parseInt(cdiv[1]) + 1) + "_0";
			var nobj = document.getElementById("div_" + next);
		}
		if (nobj != null)
		{
			editParagraph(next, "td", false);
		}
	},

	editPreviousCell: function()
	{
		// check whether next cell exists
		var prev = "";
		var cdiv = this.current_td.split("_");
		if (parseInt(cdiv[2]) > 0)
		{
			prev = "cell_" + cdiv[1] + "_" + (parseInt(cdiv[2]) - 1);
			var pobj = document.getElementById("div_" + prev);
		}
		else if (parseInt(cdiv[1]) > 0)
		{
			var p = "cell_" + (parseInt(cdiv[1]) - 1) + "_0";
			var o = document.getElementById("div_" + p);
			var i = 0;
			while (o != null)
			{
				pobj = o;
				prev = p;
				p = "cell_" + (parseInt(cdiv[1]) - 1) + "_" + i;
				var o = document.getElementById("div_" + p);
				i++;
			}
		}
		if (prev != "")
		{
			var pobj = document.getElementById("div_" + prev);
			if (pobj != null)
			{
				editParagraph(prev, "td", false);
			}
		}
	},

	////
	//// Ajax calls functions
	////

	sendCmdRequest: function(cmd, source_id, target_id, par, ajax, args, success_cb)
	{
		par['ajaxform_hier_id'] = extractHierId(source_id);
		par['command' + extractHierId(source_id)] = cmd;
		par['target[]'] = target_id;
		if (cmd == "insertJS")
		{
			par['cmd[create_par]'] = "OK";
		}
		else if (cmd != "saveDataTable")
		{
			par['cmd[exec_' + source_id + ']'] = "OK";
		}
		ilCOPage.sendFormRequest(par, ajax, args, success_cb);
	},

	// send request
	//sendRequest: function(cmd, ("command" + extractHierId(source_id) = cmd)
	// source_id, ("ajaxform_hier_id" = extractHierId(source_id);
	// target_id (target[] = target_id), mode)
	// insertJS: "cmd[create_par] = "OK"", ansonsten (außer "saveDataTable"): "cmd[exec_" + source_id + "]" = "OK"
	// saveJS, insertJS: "ajaxform_content" = tinyMCE.get('tinytarget').getContent();
	// saveJS, insertJS: "ajaxform_char" = il.AdvancedSelectionList.getHiddenInput('style_selection');
	//
	// 'saveDataTable': ajax false, ansonsten true
	sendFormRequest: function(par, ajax, args, success_cb)
	{
		var f = document.getElementById("ajaxform2");
		var k, par_el;

		while (f.hasChildNodes())
		{
			f.removeChild(f.firstChild);
		}

		for (k in par)
		{
			par_el = document.createElement('input');
			par_el.type = 'hidden';
			par_el.name = k;
			par_el.value = par[k];
			f.appendChild(par_el);
		}

		var url = f.action;

		if (!ajax)
		{
			// normal submit for submitting the whole form
			return f.submit();
		}
		else
		{
			// ajax saving
			var r = this.sendAjaxPostRequest('ajaxform2', url, args, success_cb);
		}
		return r;
	},

	// send request per ajax
	sendAjaxPostRequest: function(form_id, url, args, success_cb)
	{
		args.il = il;
		var cb =
		{
			success: success_cb,
			failure: this.handleAjaxFailure,
			argument: args
		};
		var form_str = YAHOO.util.Connect.setForm(form_id);
		var request = YAHOO.util.Connect.asyncRequest('POST', url, cb);

		return false;
	},

	handleAjaxFailure: function(o)
	{
	},

	// we got the content for editing per ajax
	editJSAjaxSuccess: function(o)
	{
		cmd_called = false;
		if(o.responseText !== undefined)
		{
//			ilCOPage.pc_id_str = "";
			o.responseText = ilCOPage.extractPCIdsFromResponse(o.responseText);
			o.responseText = ilCOPage.extractClassFromResponse(o.responseText);
			ilCOPage.removeRedundantContent();
			// paragraph editing
			var ed = tinyMCE.get('tinytarget');
			ed.setContent(o.responseText);
			var r = ed.dom.getRoot();
			r.className = "ilc_text_block_" + ilCOPage.response_class;
			ilCOPage.splitBR();
			ed.setProgressState(0); // Show progress
			ilCOPage.prepareTinyForEditing(false, o.argument.switched);
			ilCOPage.autoResize();
		}
	},

	// extract pc ids
	extractPCIdsFromResponse: function(str)
	{
		//ilCOPage.pc_id_str = "";
		ilCOPage.error_str = "";
		if (str.substr(0,3) == "###")
		{
			var end = str.indexOf("###", 3);
			ilCOPage.pc_id_str = str.substr(3,
				end - 3);
			str = str.substr(end + 3,
				str.length - (end + 3));
		}
		else
		{
			ilCOPage.error_str = str;
		}
		return str;
	},

	// extract class
	extractClassFromResponse: function(str)
	{
		var end = str.indexOf("###", 0);
		ilCOPage.response_class = str.substr(0,
			end);
		str = str.substr(end + 3,
			str.length - (end + 3));
		return str;
	},

	// quick saving has been done
	quickSavingAjaxSuccess: function(o)
	{
		$('#ilsaving').addClass("ilNoDisplay");
		ilCOPage.extractPCIdsFromResponse(o.responseText);
		if (ilCOPage.pc_id_str != "")
		{
			ed_para = ilCOPage.pc_id_str;
		}
		if (ilCOPage.error_str != "")
		{
			ilCOPage.displayError(ilCOPage.error_str);
		}
		else
		{
			if (typeof o.argument.switch_to != 'undefined' &&
				o.argument.switch_to != null)
			{
				//console.log(o.argument.switch_to);
				ilCOPage.copyInputToGhost(false);

				tinyMCE.get('tinytarget').setContent('');

				ilCOPage.removeTiny();
//				hideToolbar();

				editParagraph(o.argument.switch_to, 'edit', true);
			}
		}
	},

	// quick insert has been done
	quickInsertAjaxSuccess: function(o)
	{
		$('#ilsaving').addClass("ilNoDisplay");
		if(o.responseText !== undefined)
		{
			ilCOPage.extractPCIdsFromResponse(o.responseText);
			var pc_arr = ilCOPage.pc_id_str.split(";");
			if (ilCOPage.error_str != "")
			{
				ilCOPage.displayError(ilCOPage.error_str);
			}
			else
			{
				ilCOPage.setInsertStatus(false);
			}
//			if (o.responseText.substr(0, 3) == "---")
//			{
//				ed_para = o.responseText.substr(3, o.responseText.length - 6);
//				ilCOPage.quick_insert_id = ed_para;
//			}
		}
	},

	// default callback for successfull ajax request, reloads page content
	saveReturnAjaxSuccess: function(o)
	{
		if(o.responseText !== undefined)
		{
			var c = ilCOPage.extractPCIdsFromResponse(o.responseText);

			if (ilCOPage.pc_id_str != "")
			{
				ed_para = ilCOPage.pc_id_str;
			}

			$('#ilsaving').addClass("ilNoDisplay");

			if (ilCOPage.error_str != "")
			{
				ilCOPage.displayError(ilCOPage.error_str);
			}
			else
			{
				ilCOPage.copyInputToGhost(false);
				ilCOPage.removeTiny();
				removeToolbar();
				ilCOPage.setInsertStatus(false);

				var edit_div = document.getElementById('il_EditPage');
				$('#il_EditPage').replaceWith(c);
				ilCOPage.initDragElements();
				il.Tooltip.init();
				il.COPagePres.updateQuestionOverviews();
				il.IntLink.refresh();
				if (il.AdvancedSelectionList != null)
				{
					il.AdvancedSelectionList.init['style_selection']();
					il.AdvancedSelectionList.init['char_style_selection']();
				}

				// perform direct insert
				if (o.argument.and_new) {
					clickcmdid = ed_para;
					doActionForm('cmd[exec]', 'command', 'insert_par', '', 'PageContent', '');
				}
			}
		}
	},

	// default callback for successfull ajax request, reloads page content
	pageReloadAjaxSuccess: function(o)
	{
		if(o.responseText !== undefined)
		{
			var edit_div = document.getElementById('il_EditPage');
//			var center_td = edit_div.parentNode;
//			center_td.innerHTML = o.responseText;
			//edit_div.innerHTML = o.responseText;
			if (typeof il == 'undefined'){
				il = o.argument.il;
			}

			removeToolbar();
			$('#il_EditPage').replaceWith(o.responseText);
			ilCOPage.initDragElements();
			il.Tooltip.init();
//			ilCOPage.renderQuestions();
			il.COPagePres.updateQuestionOverviews();
			il.IntLink.refresh();
			if (il.AdvancedSelectionList != null)
			{
				il.AdvancedSelectionList.init['style_selection']();
				il.AdvancedSelectionList.init['char_style_selection']();
			}
		}
	},

	insertJSAtPlaceholder: function(cmd_id)
	{
		clickcmdid = cmd_id;
		var pl = document.getElementById('CONTENT' + cmd_id);
		pl.style.display = 'none';
		doActionForm('cmd[exec]', 'command', 'insert_par', '', 'PageContent', '');
	},

	////
	//// Table Editing
	////


	handleDataTableCommand: function (type, command)
	{
		var pars = ilCOPage.tds;
		pars["tab_cmd_type"] = type;
		pars["tab_cmd"] = command;
		pars["tab_cmd_id"] = current_row_col;
		this.sendCmdRequest("saveDataTable", ed_para, null,
			pars,
			false, null, null);

		/*		obj = document.getElementById("post");
		 hid_type = document.getElementById("dtform_type");
		 hid_type.value = type;
		 hid_cmd = document.getElementById("dtform_command");
		 hid_cmd.value = command;
		 hid_id = document.getElementById("dtform_nr");
		 hid_id.value = current_row_col;

		 obj.submit();*/
	},


	////
	//// Page editing (incl. drag/drop and menues)
	////

	/**
	 * Render questions (YUI)
	 */
	renderQuestions: function()
	{
		// get all spans
		obj=document.getElementsByTagName('div')

		// run through them
		for (var i=0;i<obj.length;i++)
		{
			// find all questions
			if(/ilc_question_/.test(obj[i].className))
			{
				var id = obj[i].id;
				if(id.substr(0, 9) == "container")
				{
					// re-draw
					id = id.substr(9);
					eval("renderILQuestion"+id+"()");
				}
			}
		}
	},

	/**
	 * Removes all paragraphs from the background that are also in the editing
	 * window (except one)
	 */
	removeRedundantContent: function()
	{
		var k, d,
			darr = ilCOPage.pc_id_str.split(";");

		for (k in darr)
		{
			if (darr[k] != ed_para)
			{
				d = document.getElementById("CONTENT" + darr[k]);
				if (d != null)
				{
					d.style.display = 'none';
				}
				d = document.getElementById("TARGET" + darr[k]);
				if (d != null)
				{
					d.style.display = 'none';
				}
			}
		}
	},

	/**
	 * Init all draggable elements (YUI)
	 */
	initDragElements: function()
	{
		var d;

		this.drag_contents = [];
		this.drag_targets = [];

		// get all spans
		obj=document.getElementsByTagName('div')

		// run through them
		for (var i=0;i<obj.length;i++)
		{
			// make all edit areas draggable
			if(/il_editarea/.test(obj[i].className))
			{
				d = new ilDragContent(obj[i].id, "gr1");
				this.drag_contents.push(d);
				//d.locked = true;
			}
			// make all drop areas dropable
			if(/il_droparea/.test(obj[i].className))
			{
				d = new ilDragTarget(obj[i].id, "gr1");
				this.drag_targets.push(d);
			}
		}
	},

	disableDragContents: function()
	{
		var i;
		for (i in this.drag_contents)
		{
			this.drag_contents[i].locked = true;
		}
	},

	enableDragContents: function()
	{
		var i;
		for (i in this.drag_contents)
		{
			this.drag_contents[i].locked = false;
		}
	}

}

il.Util.addOnLoad(function () {
	$(window).resize(ilCOPage.autoResize);
});

var stopHigh = false;
var Mposx = 0;
var Mposy = 0;
var sel_edit_areas = Array();
var edit_area_class = Array();
var edit_area_original_class = Array();
var openedMenu = "";					// menu currently opened
var current_mouse_over_id;
var cmd_called = false;

il.Util.addOnLoad(function(){var preloader = new Image();
	preloader.src = "./templates/default/images/loader.svg";});
YAHOO.util.Event.addListener(document, 'mousemove', followmouse1);


/**
 * On mouse over: Set style class of element id to class
 */
function doMouseOver (id, mclass, type, char)
{
//alert("mouseover");
	if (ilCOPage.getInsertStatus() ||
		(ilCOPage.getEditStatus() && (type != "Paragraph" || char == 'Code')))
	{
		return;
	}

	if (cmd_called) return;
	if(stopHigh) return;
	stopHigh=true;
	overId = id;
	setTimeout("stopHigh=false",10);
	obj = document.getElementById(id);
	edit_area_class[id] = mclass;
	if (obj.className != "il_editarea_selected")
	{
		edit_area_original_class[id] = obj.className;
	}
	if (sel_edit_areas[id])
	{
		obj.className = "il_editarea_active_selected";
	}
	else
	{
		if (obj.className == "il_editarea_disabled")
		{
			obj.className = "il_editarea_disabled_selected";
		}
		else
		{
			if (mclass) {
				obj.className = mclass;
			}
		}
	}

	var typetext = document.getElementById("T" + id);
	if (typetext)
	{
		typetext.style.display = '';
	}

	current_mouse_over_id = id;
}

/**
 * On mouse out: Set style class of element id to class
 */
function doMouseOut(id, mclass, type, char)
{
	if (cmd_called) return;
	if (id!=overId) return;
	stopHigh = false;
	obj = document.getElementById(id);
	if (sel_edit_areas[id])
	{
		obj.className = "il_editarea_selected";
	}
	else
	{
		//obj.className = mclass;
		obj.className = edit_area_original_class[id];
	}

	var typetext = document.getElementById("T" + id);
	if (typetext)
	{
		typetext.style.display = 'none';
	}

}

function followmouse1(e)
{
	var t = YAHOO.util.Event.getXY(e);
	Mposx = t[0];
	Mposy = t[1];
}

function showMenu(id, x, y)
{
	// no menu when paragraphs are edited
//console.log("show menu" + ilCOPage.getEditStatus());
	if (ilCOPage.getEditStatus() && ilCOPage.current_td == "")
	{
		return;
	}

	if (cmd_called) return;

	var obj = document.getElementById(id);
//console.log(obj);
	$(obj).removeClass("ilNoDisplay");
	YAHOO.util.Dom.setXY(obj, [x,y], true);
	il.Overlay.fixPosition(id);
}

function hideMenu(id, force)
{
	if (cmd_called && (typeof force == 'undefined' || !force)) return;
	obj = document.getElementById(id);
	if (obj)
	{
		$(obj).addClass("ilNoDisplay");
	}
}

var dragDropShow = false;
var mouseIsDown = false;
var mouseDownBlocked = false;
var mouseUpBlocked = false;

var dragId = "";
var overId = "";

function doMouseDown(id)
{
	if (cmd_called) return;
	//dd.elements.contextmenu.hide();
	if(mouseDownBlocked) return;
	mouseDownBlocked = true;
	setTimeout("mouseDownBlocked = false;",200);

	obj = document.getElementById(id);

	if (!mouseIsDown) {
//		dragId = id;

		oldMposx = Mposx;
		oldMposy = Mposy;
		mouseIsDown = true;
	}
}


var cmd1 = "";
var cmd2 = "";
var cmd3 = "";
var cmd4 = "";

/*function callBeforeAfterAction(setCmd3)
 {
 cmd3 = setCmd3;
 doActionForm(cmd1, cmd2, cmd3, cmd4);
 }*/


function doMouseUp(id)
{
	dragId = "";
	mouseIsDown = false;
	dragDropShow = false;
	setTimeout("dragDropShow = false",500);
}



/**
 *   on Click show context-menu at mouse-position
 */

var menuBlocked = false;
function nextMenuClick() {
	menuBlocked = false;
}


function extractHierId(id)
{
	var i = id.indexOf(":");
	if (i > 0)
	{
		id = id.substr(0, i);
	}

	return id;
}

/**
 * Process Single Mouse Click
 */
function doMouseClick(e, id, type, char)
{
	if (ilCOPage.getInsertStatus())
	{
		return;
	}

	// edit other paragaph
	if ((ilCOPage.getEditStatus() && type == "Paragraph" && char != 'Code'))
	{
		ilCOPage.switchTo(id.substr(7));
		return;
	}

	if (ilCOPage.getEditStatus() && ilCOPage.current_td == "")
	{
		return
	}

	if (cmd_called) return;

	if(menuBlocked || mouseUpBlocked) return;
	menuBlocked = true;
	setTimeout("nextMenuClick()",100);

	if (!e) var e = window.event;

	if (id.substr(0, 6) == "TARGET")
	{
		clickcmdid = id.substr(6);
		var nextMenu = "dropareamenu_" + extractHierId(clickcmdid);
	}
	else if (id.substr(0, 4) == "COL_")		// used in table data editor
	{
		clickcmdid = id.substr(4);
		var nextMenu = "col_menu_" + extractHierId(clickcmdid);
	}
	else if (id.substr(0, 4) == "ROW_")		// used in table data editor
	{
		clickcmdid = id.substr(4);
		var nextMenu = "row_menu_" + extractHierId(clickcmdid);
	}
	else
	{
		// these are the "CONTENT" ids now
		clickcmdid = id.substr(7);
//alert(clickcmdid + "*" + extractHierId(clickcmdid));
		var nextMenu = "contextmenu_" + extractHierId(clickcmdid);
	}

	var t = YAHOO.util.Event.getXY(e);
	Mposx = t[0];
	Mposy = t[1];

	if (!dragDropShow)
	{
		if (openedMenu != "" || openedMenu == nextMenu)
		{
			hideMenu(openedMenu);
			//dd.elements[openedMenu].hide();
			oldOpenedMenu = openedMenu;
			openedMenu = "";
		}
		else
		{
			oldOpenedMenu = "";
		}

		if (openedMenu == "" && nextMenu != oldOpenedMenu)
		{
			openedMenu = nextMenu;
			showMenu(openedMenu, Mposx + 2, Mposy-10);
		}
		doCloseContextMenuCounter = 40;
	}
}

/**
 * Process Double Mouse Click
 */
function doMouseDblClick(e, id)
{
	if (cmd_called) return;
	if (current_mouse_over_id == id)
	{
		obj = document.getElementById(id);
		if (sel_edit_areas[id])
		{
			sel_edit_areas[id] = false;
			obj.className = "il_editarea_active";
		}
		else
		{
			sel_edit_areas[id] = true;
			obj.className = "il_editarea_active_selected";
		}
	}
}

/**
 *   on MouseOut of context-menu hide context-menu
 */
var doCloseContextMenuCounter = -1;
function doCloseContextMenu()
{
	if (cmd_called) return;
	if (doCloseContextMenuCounter>-1)
	{
		doCloseContextMenuCounter--;
		if(doCloseContextMenuCounter==0)
		{
			if(openedMenu!="")
			{
				//dd.elements[openedMenu].hide();
				hideMenu(openedMenu);
				openedMenu = "";
				oldOpenedMenu = "";
			}
			doCloseContextMenuCounter=-1;
		}
	}
	setTimeout("doCloseContextMenu()",100);
}
setTimeout("doCloseContextMenu()",200);

var clickcmdid = 0;

var tinyinit = false;
var ed_para = null;
function editParagraph(div_id, mode, switched)
{
//	ilCOPage.setEditStatus(true);
	cmd_called = true;
	if (openedMenu != "")
	{
		hideMenu(openedMenu, true);
		oldOpenedMenu = openedMenu;
		openedMenu = "";
	}

	ed_para = div_id;
	ilCOPage.pc_id_str = "";

	if (mode == 'edit' || mode == 'multiple')
	{
		// get paragraph edit div
		var pdiv = document.getElementById("CONTENT" + div_id);
		var pdiv_reg = YAHOO.util.Region.getRegion(pdiv);
	}

	if (mode == 'insert')
	{
		// get placeholder div
		var pdiv = document.getElementById("TARGET" + div_id);
//console.log(pdiv);
		var insert_ghost = new YAHOO.util.Element(document.createElement('div'));
		insert_ghost = YAHOO.util.Dom.insertAfter(insert_ghost, pdiv);
		insert_ghost.id = "insert_ghost";
		insert_ghost.style.paddingTop = "5px";
		insert_ghost.style.paddingBottom = "5px";

		var pdiv_reg = YAHOO.util.Region.getRegion(pdiv);
	}

	// table editing mode (td)
	var moved = false;		// is edit area currently move from one td to another?
	if (mode == 'td')
	{
		// if current_td already set, we must move editor to new td
		if (ilCOPage.current_td != "")
		{
			ilCOPage.copyInputToGhost(true);
			ilCOPage.copyInputToGhost(false);
			var pdiv = document.getElementById('div_' + ilCOPage.current_td);
			pdiv.style.minHeight = '';
			pdiv.style.minWidth = '';
			moved = true;
		}

		// get placeholder div
		var pdiv = document.getElementById('div_' + div_id);
		var pdiv_reg = YAHOO.util.Region.getRegion(pdiv);
		ilCOPage.current_td = div_id;
	}


	// set background "ghost" element
	if (mode == 'td')
	{
		ilCOPage.edit_ghost = "div_" + ilCOPage.current_td;
//ilCOPage.edit_ghost = "td_" + ilCOPage.current_td;
	}
	else if (mode == 'insert')
	{
		ilCOPage.edit_ghost = "insert_ghost";
	}
	else
	{
		ilCOPage.edit_ghost = "CONTENT" + ed_para;
	}

	// disable drag content
	ilCOPage.disableDragContents();


//console.log("content_css: " + ilCOPage.content_css);
//	if (!tinyinit) {
// content_css: "Services/COPage/css/content.css, templates/default/delos.css",
// theme_advanced_buttons2 : "table,|,row_props,cell_props,|,row_before,row_after,delete_row,|,col_before,col_after,delete_col,|,split_cells,merge_cells",

	if (switched)
	{
		var ta = document.getElementById('tinytarget');
		if (ta != null)
		{
			var ta_par = ta.parentNode;
			ta_par.removeChild(ta);
		}
	}

	// create new text area for tiny
	if (!moved)
	{
		//var pdiv_width = pdiv_reg.right - pdiv_reg.left;
		var ta_div = new YAHOO.util.Element(document.createElement('div'));

		var ta = new YAHOO.util.Element(document.createElement('textarea'));
		//ta = YAHOO.util.Dom.insertAfter(ta, pdiv);
		ta = ta_div.appendChild(ta);
		ta.id = 'tinytarget';
		ta.className = 'par_textarea';
		ta.style.height = '1px';

		if (ilCOPage.current_td != "")
		{
			// this should be the table
			var ins_div = pdiv.parentNode.parentNode.parentNode.parentNode;
		}
		else
		{
			var ins_div = pdiv;
		}

		ta_div = YAHOO.util.Dom.insertAfter(ta_div, ins_div);
		ta_div.id = 'tinytarget_div';
		ta_div.style.position = 'absolute';
		ta_div.style.left = '-200px';

	}

	// init tiny
	var resize = false;
	var show_path = false;
	var statusbar = false;

	// for debugging, this may be activated
	if (false && mode != 'td')
	{
		show_path = true;
		statusbar = 'bottom';
	}

	var tinytarget = document.getElementById("tinytarget");
	tinytarget.style.display = '';
	if (!moved)
	{
		tinyMCE.init({
			/* part of 4 */
			toolbar: false,
			menubar: false,
			statusbar: false,
			theme : "modern",
			language : "en",
			plugins : "save,paste",
			save_onsavecallback : "saveParagraph",
			mode : "exact",
			elements: "tinytarget",
			content_css: ilCOPage.content_css,
			fix_list_elements : true,
			valid_elements : "p,br[_moz_dirty],span[class],code,ul[class],ol[class],li[class]",
			forced_root_block : 'p',
			entity_encoding : "raw",
			paste_remove_styles: true,
			formats : {
				Strong: {inline : 'span', classes : 'ilc_text_inline_Strong'},
				Emph: {inline : 'span', classes : 'ilc_text_inline_Emph'},
				Important: {inline : 'span', classes : 'ilc_text_inline_Important'},
				Comment: {inline : 'span', classes : 'ilc_text_inline_Comment'},
				Quotation: {inline : 'span', classes : 'ilc_text_inline_Quotation'},
				Accent: {inline : 'span', classes : 'ilc_text_inline_Accent'}
			},
			/* not found in 4 code or docu (the configs for p/br are defaults for 3, so this should be ok) */
			removeformat_selector : 'span,code',
			remove_linebreaks : true,
			convert_newlines_to_brs : false,
			force_p_newlines : true,
			force_br_newlines : false,
			/* not found in 3 docu (anymore?) */
			cleanup_on_startup : true,
			cleanup: true,
			paste_auto_cleanup_on_paste : true,


			/**
			 * Event is triggered after the paste plugin put the content
			 * that should be pasted into a dom structure now
			 * BUT the content is not put into the document yet
			 *
			 * still exists in 4
			 */
			paste_preprocess: function (pl, o) {
				var ed = ed = tinyMCE.activeEditor;

				if (o.wordContent)
				{
					o.content = o.content.replace(/(\r\n|\r|\n)/g, '\n');
					o.content = o.content.replace(/(\n)/g, ' ');
				}
				// remove any attributes from <p>
				o.content = o.content.replace(/(<p [^>]*>)/g, '<p>');
				//o.content = o.content.replace(/(<p>)/g, '');
				//o.content = o.content.replace(/(<\/p>)/g, '<br />');

				// remove all divs
				o.content = o.content.replace(/(<div [^>]*>)/g, '');
				o.content = o.content.replace(/(<\/div>)/g, '');
			},

			/**
			 * Event is triggered after the paste plugin put the content
			 * that should be pasted into a dom structure now
			 * BUT the content is not put into the document yet
			 *
			 * still exists in 4
			 */
			paste_postprocess: function (pl, o) {
				var ed = ed = tinyMCE.activeEditor;

				if (o.wordContent)
				{

				}

				// we must handle all valid elements here
				// p (handled in paste_preprocess)
				// br[_moz_dirty] (investigate)
				// span[class] (todo)
				// code (should be ok, since no attributes allowed)
				// ul[class],ol[class],li[class] handled here

				// fix lists
				ed.dom.setAttrib(ed.dom.select('ol', o.node), 'class', 'ilc_list_o_NumberedList');
				ed.dom.setAttrib(ed.dom.select('ul', o.node), 'class', 'ilc_list_u_BulletedList');
				ed.dom.setAttrib(ed.dom.select('li', o.node), 'class', 'ilc_list_item_StandardListItem');

				// replace all b nodes by spans[Strong]
				tinymce.each(ed.dom.select('b', o.node), function(n) {
					ed.dom.replace(ed.dom.create('span', {'class': 'ilc_text_inline_Strong'}, n.innerHTML), n);
				});
				// replace all u nodes by spans[Important]
				tinymce.each(ed.dom.select('u', o.node), function(n) {
					ed.dom.replace(ed.dom.create('span', {'class': 'ilc_text_inline_Important'}, n.innerHTML), n);
				});
				// replace all i nodes by spans[Emph]
				tinymce.each(ed.dom.select('i', o.node), function(n) {
					ed.dom.replace(ed.dom.create('span', {'class': 'ilc_text_inline_Emph'}, n.innerHTML), n);
				});

				// remove all id attributes from the content
				tinyMCE.each(ed.dom.select('*[id!=""]', o.node), function(el) {
					el.id = '';
				});
				ilCOPage.pasting = true;
			},

			setup : function(ed) {
				ed.on('KeyUp', function(ev)
				{
					var ed = tinyMCE.get('tinytarget');
//console.log("onKeyPress");
					ilCOPage.autoResize(ed);
				});
				ed.on('KeyDown', function(ev)
				{
					var ed = tinyMCE.get('tinytarget');

					if(ev.keyCode == 35 || ev.keyCode == 36)
					{
						var isMac = navigator.platform.toUpperCase().indexOf('MAC')>=0;
						if (!ev.shiftKey && isMac) {
							YAHOO.util.Event.preventDefault(ev);
							YAHOO.util.Event.stopPropagation(ev);
						}
					}

					if(ev.keyCode == 9 && !ev.shiftKey)
					{
//						console.log("tab");
						YAHOO.util.Event.preventDefault(ev);
						YAHOO.util.Event.stopPropagation(ev);
						if (ilCOPage.current_td != "")
						{
							ilCOPage.editNextCell();
						}
						else
						{
							if (ed.queryCommandState('InsertUnorderedList') ||
								ed.queryCommandState('InsertOrderedList'))
							{
								ilCOPage.cmdListIndent();
							}
						}
					}
					if(ev.keyCode == 9 && ev.shiftKey)
					{
//						console.log("backtab");
						YAHOO.util.Event.preventDefault(ev);
						YAHOO.util.Event.stopPropagation(ev);
						if (ilCOPage.current_td != "")
						{
							ilCOPage.editPreviousCell();
						}
						else
						{
							if (ed.queryCommandState('InsertUnorderedList') ||
								ed.queryCommandState('InsertOrderedList'))
							{
								ilCOPage.cmdListOutdent();
							}
						}
					}
					//console.log("onKeyDown");
				});
				ed.on('NodeChange', function(cm, n)
				{
					var ed = tinyMCE.get('tinytarget');
//console.log("onNodeChange");
//console.log("----");
//console.trace();
					// clean content after paste (has this really an effect?)
					// (yes, it does, at least splitSpans is important here #13019)
					if (ilCOPage.pasting) {
						ilCOPage.pasting = false;
						ilCOPage.splitDivs();
						ilCOPage.fixListClasses(false);
						ilCOPage.splitSpans();
					}

					// update state of indent/outdent buttons
					var ibut = document.getElementById('ilIndentBut');
					var obut = document.getElementById('ilOutdentBut');
					if (ibut != null && obut != null)
					{
						if (ed.queryCommandState('InsertUnorderedList') ||
							ed.queryCommandState('InsertOrderedList'))
						{
							ibut.style.visibility = '';
							obut.style.visibility = '';
						}
						else
						{
							ibut.style.visibility = 'hidden';
							obut.style.visibility = 'hidden';
						}
					}

					ilCOPage.updateMenuButtons();

				});

				var width = pdiv_reg.width;
				var height = pdiv_reg.height;
				if (width < ilCOPage.minwidth)
				{
					width = ilCOPage.minwidth;
				}
				if (height < ilCOPage.minheight)
				{
					height = ilCOPage.minheight;
				}

				//ed.onInit.add(function(ed, evt)
				ed.on('init', function(evt)
				{
					var ed = tinyMCE.get('tinytarget');

					ilCOPage.setEditFrameSize(width, height);
					if (mode == 'edit')
					{
						pdiv.style.display = "none";
					}

					if (mode == 'edit')
					{

						var tinytarget = document.getElementById("tinytarget_div");
						ta_div.style.position = '';
						ta_div.style.left = '';

						ed.setProgressState(1); // Show progress
//alert("1");
						// get content per ajax
						ilCOPage.sendCmdRequest("editJS", div_id, null, {},
							true, {switched: switched}, ilCOPage.editJSAjaxSuccess);
					}

					if (mode == 'multiple')
					{
						var tinytarget = document.getElementById("tinytarget_div");
						ta_div.style.position = '';
						ta_div.style.left = '';
						// get content per ajax
						ed.setProgressState(1); // Show progress
						ilCOPage.sendCmdRequest("editMultipleJS", div_id, null, {},
							true, {switched: switched}, ilCOPage.editJSAjaxSuccess);
					}

					if (mode == 'insert')
					{
						ed.setContent("<p></p>");
//				console.log(ed.getContent());
						var snode = ed.dom.getRoot();
						snode.className = 'ilc_text_block_Standard';
						ilCOPage.prepareTinyForEditing(true);
						ilCOPage.synchInputRegion();
						ilCOPage.focusTiny(true);
						//		setTimeout('ilCOPage.focusTiny();', 1000);
						cmd_called = false;
//				console.log(ed.getContent());
					}

					if (mode == 'td')
					{
//console.log("Setting content to: " + pdiv.innerHTML);
						ed.setContent(pdiv.innerHTML);
						ilCOPage.splitBR();
						ilCOPage.prepareTinyForEditing(false, false);
						ilCOPage.synchInputRegion();
						ilCOPage.focusTiny(true);
						cmd_called = false;
					}
				});
			}

		});
	}
	else	// moved (table editing)
	{
		//prepareTinyForEditing;
		tinyMCE.execCommand('mceToggleEditor', false, 'tinytarget');
		var ed = tinyMCE.get('tinytarget');
		ed.setContent(pdiv.innerHTML);
		ilCOPage.splitBR();
//console.log("Setting content to: " + pdiv.innerHTML);
//		ilCOPage.prepareTinyForEditing(true, false);
		ilCOPage.synchInputRegion();
		ilCOPage.focusTiny(false);
		cmd_called = false;
	}

	tinyinit = true;
}


function eventT(ed)
{
	// window vs document
//	console.log(window);
//	console.log(tinymce.dom.Event);
	tinymce.dom.Event.add(tinymce.dom.doc, 'mousedown',
		function() {console.log("mouse down");}
		, false);
}

/**
 * Save paragraph
 */
function saveParagraph()
{
	ilCOPage.cmdSave();
}

function doActionForm(cmd, command, value, target, type, char)
{
	if (cmd_called) return;
//alert("-" + cmd + "-" + command + "-" + value + "-" + target + "-"+ type + "-" + char + "-");
//alert(clickcmdid);
//-cmd[exec]-command-edit--
	doCloseContextMenuCounter = 2;

	if(cmd=="cmd[exec]")
	{
		cmd = "cmd[exec_"+clickcmdid+"]";
	}

	if (command=="command")
	{
		command += extractHierId(clickcmdid);
	}
//console.trace();
//alert("-" + cmd + "-" + command + "-" + value + "-" + target + "-" + type + "-" + clickcmdid + "-");
//-cmd[exec_1:1d3ae9ffebd59671a8c7e254e22d3b5d]-command1-edit--

	if (value=="edit" && type=="Paragraph" && char != "Code")
	{
		editParagraph(clickcmdid, 'edit', false);
		return false;
	}

	if (value=="editMultiple" && type=="Paragraph" && char != "Code")
	{
		editParagraph(clickcmdid, 'multiple', false);
		return false;
	}

	if (value == 'insert_par')
	{
		editParagraph(clickcmdid, 'insert', false);
		return false;
	}

	if (value=="delete")
	{
		if(!confirm(confirm_delete))
		{
			menuBlocked = true;
			setTimeout("nextMenuClick()",500);
			return;
		}
		menuBlocked = true;
		setTimeout("nextMenuClick()",500);
	}

	//alert(target+" - "+command+" - "+value+" - "+cmd);

	/*
	 html = "<form name=cmform id=cmform method=post action='"+actionUrl+"'>";
	 html += "<input type=hidden name='target[]' value='"+target+"'>";
	 html += "<input type=hidden name='"+command+"' value='"+value+"'>";
	 html += "<input type=hidden name='"+cmd+"' value='Ok'>";
	 html += "</form>";

	 dd.elements.actionForm.write(html);
	 */
	obj = document.getElementById("cmform");
	hid_target = document.getElementById("cmform_target");
	hid_target.value = target;
	hid_cmd = document.getElementById("cmform_cmd");
	hid_cmd.name = command;
	hid_cmd.value = value;
	hid_exec = document.getElementById("cmform_exec");
	hid_exec.name = cmd;

	cmd_called = true;

	if (ccell)
	{
		var loadergif = document.createElement('img');
		loadergif.src = "./templates/default/images/loader.svg";
		loadergif.border = 0;
		//loadergif.style.position = 'absolute';
		ccell.bgColor='';
		ccell.appendChild(loadergif);
	}
	obj.submit();
}

var ccell = null;

function M_in(cell)
{
	if (cmd_called) return;
	doCloseContextMenuCounter=-1;
	ccell = cell;
}
function M_out(cell)
{
	if (cmd_called) return;
	doCloseContextMenuCounter=5;
	ccell = null;
}

var oldMposx = -1;
var oldMposy = -1;


// This will be our extended DDProxy object
ilDragContent = function(id, sGroup, config)
{
	this.swapInit(id, sGroup, config);
	this.isTarget = false;
};

// We are extending DDProxy now
YAHOO.extend(ilDragContent, YAHOO.util.DDProxy);

// protype: all instances will get this functions
ilDragContent.prototype.swapInit = function(id, sGroup, config)
{
	if (!id) { return; }
	this.init(id, sGroup, config);	// important!
	this.initFrame();				// important!
};

// overwriting onDragDrop function
// (ending a valid drag drop operation)
ilDragContent.prototype.onDragDrop = function(e, id)
{
	target_id = id.substr(6);
	source_id = this.id.substr(7);
	if (source_id != target_id)
	{
		ilCOPage.sendCmdRequest("moveAfter", source_id, target_id, {},
			true, {}, ilCOPage.pageReloadAjaxSuccess);
	}
};


ilDragContent.prototype.endDrag = function(e)
{
};

// overwriting onDragDrop function
ilDragContent.prototype.onDragEnter = function(e, id) {
	target_id = id.substr(6);
	source_id = this.id.substr(7);
	if (source_id != target_id) {
		$(document.getElementById(id)).addClass("ilCOPGDropActice");
	}
};

// overwriting onDragDrop function
ilDragContent.prototype.onDragOut = function(e, id) {
	$(document.getElementById(id)).removeClass("ilCOPGDropActice");
};

///
///   ilDragTarget
///

// This will be our extended DDProxy object
ilDragTarget = function(id, sGroup, config)
{
	this.dInit(id, sGroup, config);
};

// We are extending DDProxy now
//YAHOO.extend(ilDragTarget, YAHOO.util.DDProxy);
YAHOO.extend(ilDragTarget, YAHOO.util.DDTarget);

// protype: all instances will get this functions
ilDragTarget.prototype.dInit = function(id, sGroup, config)
{
	if (!id) { return; }
	this.init(id, sGroup, config);	// important!
	//this.initFrame();				// important!
};


function ilEditMultiAction(cmd)
{
	if (cmd == "selectAll")
	{
		var divs = $("div.il_editarea");
		if (divs.length > 0)
		{
			for (var i = 0; i < divs.length; i++)
			{
				sel_edit_areas[divs[i].id] = true;
				divs[i].className = "il_editarea_selected";
			}
		}
		else
		{
			var divs = $("div.il_editarea_selected");
			for (var i = 0; i < divs.length; i++)
			{
				sel_edit_areas[divs[i].id] = false;
				divs[i].className = "il_editarea";
			}
		}

		return false;
	}


	hid_exec = document.getElementById("cmform_exec");
	hid_exec.name = "cmd[" + cmd + "]";
	hid_cmd = document.getElementById("cmform_cmd");
	hid_cmd.name = cmd;
	form = document.getElementById("cmform");

	var sel_ids = "";
	var delim = "";
	for (var key in sel_edit_areas)
	{
		if (sel_edit_areas[key])
		{
			sel_ids = sel_ids + delim + key.substr(7);
			delim = ";";
		}
	}

	hid_target = document.getElementById("cmform_target");
	hid_target.value = sel_ids;

	form.submit();

	return false;
}

//
// js paragraph editing
//

// copied from TinyMCE editor_template_src.js
function showToolbar(ed_id)
{
// todo tinynew

	$("#tinytarget_ifr").parent().css("border-width", "0px");
	$("#tinytarget_ifr").parent().parent().parent().css("border-width", "0px");

	/*	var DOM = tinyMCE.DOM, obj;
	 var Event = tinyMCE.dom.Event;
	 var e = DOM.get(ed_id + '_external');
	 DOM.show(e);


	 var f = Event.add(ed_id + '_external_close', 'click', function() {
	 DOM.hide(ed_id + '_external');
	 Event.remove(ed_id + '_external_close', 'click', f);
	 });

	 DOM.show(e);*/

	if (false)
	{
		DOM.setStyle(e, 'top', 0 - DOM.getRect(ed_id + '_tblext').h - 1);
	}
	else
	{
		// move parent node to end of body to ensure layer being on top
		if (!ilCOPage.menu_panel) {
			obj = document.getElementById('iltinymenu');
			$(obj).appendTo("body");
			obj = document.getElementById('ilEditorPanel');
			// if statement added since this may miss if internal links not supported?
			// e.g. table editing
			if (obj) {
				$(obj.parentNode).appendTo("body");
			}
		}

		$('#ilsaving').addClass("ilNoDisplay");

		// make tinymenu a panel
		obj = document.getElementById('iltinymenu');
		obj.style.display = "";
		ilCOPage.menu_panel = true;

// todo tinynew
//		DOM.setStyle(e, 'left', -6000);
//		var ed_el = document.getElementById(ed_id + '_parent');
		var m_el = document.getElementById('iltinymenu');
//		var ed_reg = YAHOO.util.Region.getRegion(ed_el);
		var m_reg = YAHOO.util.Region.getRegion(m_el);
		var debug = 0;

	}

// todo tinynew
	// Fixes IE rendering bug
//	DOM.hide(e);
//	DOM.show(e);
//	e.style.filter = '';


	e = null;
};

function hideToolbar () {
	obj = document.getElementById('iltinymenu');
	obj.style.display = "none";
}

function removeToolbar () {
//console.log("removing toolbar");
	if (ilCOPage.menu_panel) {
		var obj = document.getElementById('iltinymenu');
		$(obj).remove();

		ilCOPage.menu_panel = null;

		// this element exists, if internal link panel has been clicked
		var obj = document.getElementById('ilEditorPanel_c');
		if (obj && obj.parentNode) {
			$(obj.parentNode).remove();
		}

		// this element still exists, if interna link panel has not been clicked
		var obj = document.getElementById('ilEditorPanel');
		if (obj && obj.parentNode) {
			$(obj.parentNode).remove();
		}
	}
}

// dynamically set "media disabled" text size
il.Util.addOnLoad(function() { $(".ilCOPGMediaDisabled").each(function () {
	var t = $(this),
		max = (t.height() > 50)
			? 50
			: 18;
	t.css('font-size', Math.max(Math.min(t.width() / 5, max), 10) + "px");
})});


//il.Util.addOnLoad(function(){ilCOPage.editTD('cell_0_0');});

var current_row_col;
