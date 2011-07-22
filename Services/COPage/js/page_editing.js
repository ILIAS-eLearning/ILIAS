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

	switchDebugGhost: function() {
		var tp = document.getElementById('tinytarget_parent');
		if (!this.ghost_debugged)
		{
			tp.style.display = 'none';
			this.ghost_debugged = true;
		}
		else
		{
			tp.style.display = '';
			this.ghost_debugged = false;
		}
	},
	
	setContentCss: function (content_css)
	{
		this.content_css = content_css;
	},

	cmdSave: function (switch_to)
	{
		if (ilCOPage.current_td != "")
		{
			return this.cmdSaveReturn();
		}
		
		var el = document.getElementById('ilsaving');
		el.style.display = '';
		if (this.getInsertStatus())
		{
//			ilFormSend("insertJS", ed_para, null, "saveonly");
			var content = tinyMCE.get('tinytarget').getContent();
			var style_class = ilAdvancedSelectionList.getHiddenInput('style_selection');
			//this.copyInputToGhost(false);
			//this.removeTiny();
			this.sendCmdRequest("insertJS", ed_para, null,
				{ajaxform_content: content,
				pc_id_str: this.pc_id_str,
				ajaxform_char: style_class,
				insert_at_id: ed_para,
				quick_save: 1},
				true, {switch_to: switch_to}, this.quickInsertAjaxSuccess);
		}
		else
		{
			//ilFormSend("saveJS", ed_para, null, "saveonly");
			var content = tinyMCE.get('tinytarget').getContent();
			var style_class = ilAdvancedSelectionList.getHiddenInput('style_selection');
			//this.copyInputToGhost(false);
			//this.removeTiny();

			this.sendCmdRequest("saveJS", ed_para, null,
				{ajaxform_content: content,
				pc_id_str: this.pc_id_str,
				ajaxform_char: style_class,
				quick_save: 1},
				true, {switch_to: switch_to}, this.quickSavingAjaxSuccess);

		}
	},
	
	cmdSaveReturn: function ()
	{
		var el = document.getElementById('ilsaving');
		el.style.display = '';
		var ed = tinyMCE.get('tinytarget');
		this.autoResize(ed);
		this.setEditStatus(false);
		if (ilCOPage.current_td != "")
		{
			//ilFormSend("saveDataTable", ed_para, null, null);
			
			tbl = document.getElementById("ed_datatable");
			this.sendCmdRequest("saveDataTable", ed_para, null,
				{ajaxform_content: tbl.innerHTML},
				false, null, null);
		}
		else if (this.getInsertStatus() && !ilCOPage.quick_insert_id)
		{
			var content = tinyMCE.get('tinytarget').getContent();
			var style_class = ilAdvancedSelectionList.getHiddenInput('style_selection');
			this.copyInputToGhost(false);
			this.removeTiny();
			this.setInsertStatus(false);
			this.sendCmdRequest("insertJS", ed_para, null,
				{ajaxform_content: content,
				pc_id_str: this.pc_id_str,
				insert_at_id: ed_para,
				ajaxform_char: style_class},
				true, {}, this.pageReloadAjaxSuccess);
		}
		else
		{
			var content = tinyMCE.get('tinytarget').getContent();
			var style_class = ilAdvancedSelectionList.getHiddenInput('style_selection');
			this.copyInputToGhost(false);
			this.removeTiny();
			this.sendCmdRequest("saveJS", ed_para, null,
				{ajaxform_content: content,
				pc_id_str: this.pc_id_str,
				ajaxform_char: style_class},
				true, {}, this.pageReloadAjaxSuccess);
		}
	},

	switchTo: function(pc_id)
	{
		this.cmdSave(pc_id);
	},
	
	cmdCancel: function ()
	{
		//var el = document.getElementById('ilsaving');
		//el.style.display = '';
		var ed = tinyMCE.get('tinytarget');
		this.autoResize(ed);
		this.setEditStatus(false);
		this.setInsertStatus(false);
		this.copyInputToGhost(false);
		this.removeTiny();
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

		var st_sel = ed.controlManager.get('styleselect');

		// from tiny_mce_src-> renderMenu
		if (st_sel.settings.onselect('style_' + stype[t]) !== false)
			st_sel.select('style_' + stype[t]); // Must be runned after
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

	addBBCode: function(stag, etag)
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
			r.setEnd(rcopy.endContainer.nextSibling, stag.length);
			r.setStart(rcopy.startContainer.nextSibling, stag.length);
			ed.selection.setRng(r);
		}
		else
		{
			ed.selection.setContent(stag + ed.selection.getContent() + etag);
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
		this.fixListClasses();
		this.autoResize(ed);
	},

	cmdNList: function()
	{
		var ed = tinyMCE.get('tinytarget');
		ed.focus();
		ed.execCommand('InsertOrderedList', false);
		this.fixListClasses();
		this.autoResize(ed);
	},

	cmdListIndent: function()
	{
		var ed = tinyMCE.get('tinytarget');
		ed.focus();
		ed.execCommand('Indent', false);
		this.fixListClasses();
		this.autoResize(ed);
	},

	cmdListOutdent: function()
	{
		var ed = tinyMCE.get('tinytarget');
		ed.focus();
		ed.execCommand('Outdent', false);
		this.fixListClasses();
		this.autoResize(ed);
	},
	
	fixListClasses: function()
	{
		tinyMCE.activeEditor.dom.addClass(tinyMCE.activeEditor.dom.select('ol'), 'ilc_list_o_NumberedList');
		tinyMCE.activeEditor.dom.addClass(tinyMCE.activeEditor.dom.select('ul'), 'ilc_list_u_BulletedList');
		tinyMCE.activeEditor.dom.addClass(tinyMCE.activeEditor.dom.select('li'), 'ilc_list_item_StandardListItem');
	},
	
	cleanContent: function()
	{
		// plit all divs in divs
		var ed = tinyMCE.activeEditor;
		var divs = ed.dom.select('div > div');
		var k
		for (k in divs)
		{
			ed.dom.split(divs[k].parentNode, divs[k]);
		}
	},

	setEditStatus: function(status)
	{
//console.log("set edit status " + status);
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

	setParagraphClass: function(i)
	{
		var ed = tinyMCE.activeEditor;
		ed.focus();
		var snode = ilCOPage.getCurrentDivNode();
		
		if (snode)
		{
			snode.className = "ilc_text_block_" + i['hid_val'];
		}
		this.autoResize(ed);
	},

	cmdNewParagraph: function(i)
	{
		var ed = tinyMCE.activeEditor;
		ed.focus();
		var snode = ilCOPage.getCurrentDivNode();
		var el = ed.dom.create('div', {'class' : 'ilc_text_block_Standard'}, '&nbsp;');
		ed.dom.insertAfter(el, snode);
		
		//rcopy = ed.selection.getRng().cloneRange();
		//ed.selection.setContent('</div><div class="ilc_text_block_Standard">' + ed.selection.getContent());
		r =  ed.dom.createRng();
		r.setEnd(snode.nextSibling, 0);
		r.setStart(snode.nextSibling, 0);
		ed.selection.setRng(r);
		ed.focus();

		this.autoResize(ed);
	},
	
	
	/**
	 * Very important for IE: If the event that triggers the action is triggered
	 * by an <a> Tag without href attribute. The selection in the editor will
	 * be messed up. No idea why this happens. Add href="#" to all anchors!
	 */
	getCurrentDivNode: function()
	{
		var ed = tinyMCE.activeEditor;
		ed.focus();
		var nnode = ed.selection.getStart();
		var snode;
		while (nnode && (nnode.nodeName.toLowerCase() != "body"))
		{
			snode = nnode;
			nnode = nnode.parentNode;
		}
		if (snode.nodeName.toLowerCase() == "div")
		{
			return snode;
		}

		return false;
	},

	prepareTinyForEditing: function(insert, switched)
	{
		var ed = tinyMCE.getInstanceById('tinytarget');
		tinyMCE.execCommand('mceAddControl', false, 'tinytarget');
		
		if (!switched)
		{
			showToolbar('tinytarget');
		}

//console.log("prepareTiny");
//		if (!insert)
//		{
//console.log("no insert");
			tinyifr = document.getElementById("tinytarget_parent");
			tinyifr.style.position = "absolute";
//			this.synchInputRegion();
//		}
		
		this.setEditStatus(true);
		this.setInsertStatus(insert);
		this.focusTiny();
		//this.autoScroll();
		this.copyInputToGhost(true);
		this.synchInputRegion();
		this.updateMenuButtons();
	},

	focusTiny: function(insert)
	{
		var ed = tinyMCE.getInstanceById('tinytarget');
		if (ed)
		{
			var e = tinyMCE.DOM.get(ed.id + '_external');
			var r = ed.dom.getRoot();
			var fc = r.childNodes[0];
			if (fc != null)
			{
				// set selection to start of first div
				var rn = ed.dom.createRng();
				rn.setStart(fc, 0);
				rn.setEnd(fc, 0);
				ed.selection.setRng(rn);
				if (fc.className != null)
				{
					var st = fc.className.substring(15);
					var st_s = document.getElementById('style_selection');
					if (st_s != null)
					{
						ilAdvancedSelectionList.selectItem('style_selection', st);
					}
				}
			}

			// without the timeout, cursor will disappear, e.g. in firefox when
			// new paragraph is inserted
			setTimeout('tinyMCE.execCommand(\'mceFocus\',false,\'tinytarget\');', 1);
		}
	},

	removeTiny: function() {
		tinyMCE.execCommand('mceRemoveControl', false, 'tinytarget');
		var tt = document.getElementById("tinytarget");
		tt.style.display = 'none';
	},
	
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

	// set frame size of editor
	setEditFrameSize: function(width, height)
	{
		var tinyifr = document.getElementById("tinytarget_ifr");
		var tinytd = document.getElementById("tinytarget_tbl");;
		tinyifr.style.width = width + "px";
		tinyifr.style.height = height + "px";
		tinytd.style.width = width + "px";
		tinytd.style.height = height + "px";
		this.ed_width = width;
		this.ed_height = height;
	},

	// copy input of tiny to ghost div in background
	copyInputToGhost: function(add_final_spacer)
	{
		var ed = tinyMCE.get('tinytarget');

		if (this.edit_ghost)
		{
			var pdiv = document.getElementById(this.edit_ghost);
			if (pdiv)
			{
				var c = ed.getContent();
				var e = c.substr(c.length - 6);
				var b = c.substr(c.length - 12, 6);
				if (e == "</div>" && b != "<br />" && add_final_spacer)
				{
					// ensure at least one more line of space
					c = c.substr(0, c.length - 6) + "<br />.</div>";
				}
				pdiv.innerHTML = c;
			}
		}
	},

	// synchs the size/position of the tiny to the space the ghost
	// object uses in the background
	synchInputRegion: function()
	{
		var back_el;
		
		if (this.current_td)
		{
			back_el = document.getElementById(this.edit_ghost);
			back_el = back_el.parentNode;
		}
		else
		{
			ilCOPage.autoScroll();
			back_el = document.getElementById(this.edit_ghost);
		}

		back_el.style.minHeight = ilCOPage.minheight + "px";
		back_el.style.minWidth = ilCOPage.minwidth + "px";

		tinyifr = document.getElementById("tinytarget_parent");

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
		
		YAHOO.util.Dom.setX(tinyifr, back_reg.x);
		YAHOO.util.Dom.setY(tinyifr, back_reg.y+1);
		this.setEditFrameSize(back_reg.width-2,
			back_reg.height);
	},

	autoResize: function(ed)
	{
		this.copyInputToGhost(true);
		this.synchInputRegion();
	},
	
	// scrolls position of editor under editor menu
	autoScroll: function()
	{
		var tinyifr = document.getElementById("tinytarget_parent");
		var menu = document.getElementById('iltinymenu');

		if (tinyifr && menu)
		{
			var tiny_reg = YAHOO.util.Region.getRegion(tinyifr);
			var menu_reg = YAHOO.util.Region.getRegion(menu);
			var cl_reg = YAHOO.util.Dom.getClientRegion();
			window.scrollTo(0, -20 + tiny_reg.y - (menu_reg.height + menu_reg.y - cl_reg.top));
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
	},

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
	// saveJS, insertJS: "ajaxform_char" = ilAdvancedSelectionList.getHiddenInput('style_selection');
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
			ilCOPage.removeRedundantContent();
			// paragraph editing
			var ed = tinyMCE.getInstanceById('tinytarget');
			ed.setContent(o.responseText);
			ed.setProgressState(0); // Show progress
			ilCOPage.prepareTinyForEditing(false, o.argument.switched);
			ilCOPage.autoResize();
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
	
	// extract pc ids
	extractPCIdsFromResponse: function(str)
	{
		ilCOPage.pc_id_str = "";
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
	
	// quick saving has been done
	quickSavingAjaxSuccess: function(o)
	{
		var el = document.getElementById('ilsaving');
		el.style.display = 'none';
		ilCOPage.extractPCIdsFromResponse(o.responseText);
		var pc_arr = ilCOPage.pc_id_str.split(";");
		ed_para = pc_arr[0];
		if (ilCOPage.error_str != "")
		{
			ilCOPage.displayError(ilCOPage.error_str);
		}
		
		if (typeof o.argument.switch_to != 'undefined' &&
			o.argument.switch_to != null)
		{
//console.log(o.argument.switch_to);
			ilCOPage.copyInputToGhost(false);

			tinyMCE.get('tinytarget').setContent('');
			ilCOPage.removeTiny();
			editParagraph(o.argument.switch_to, 'edit', true);
		}
	},

	// quick insert has been done
	quickInsertAjaxSuccess: function(o)
	{
		var el = document.getElementById('ilsaving');
		el.style.display = 'none';
		if(o.responseText !== undefined)
		{
			ilCOPage.extractPCIdsFromResponse(o.responseText);
			var pc_arr = ilCOPage.pc_id_str.split(";");
			if (ilCOPage.error_str != "")
			{
				ilCOPage.displayError(ilCOPage.error_str);
			}
//			if (o.responseText.substr(0, 3) == "---")
//			{
//				ed_para = o.responseText.substr(3, o.responseText.length - 6);
//				ilCOPage.quick_insert_id = ed_para;
//			}
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
			edit_div.innerHTML = o.responseText;
			ilCOPage.initDragElements();
			ilTooltip.init();
			ilCOPage.renderQuestions();
			if (ilAdvancedSelectionList != null)
			{
				ilAdvancedSelectionList.init['style_selection']();
				ilAdvancedSelectionList.init['char_style_selection']();
			}
		}
	},
	
	// display error
	displayError: function(str)
	{
		var ediv = document.createElement('div');
		var mc = document.getElementById("il_CenterColumn");
		var estr;
		
		estr = "Sorry, an error occured. Please copy the content of this window and report the error at:<br /> " +
			"<a href='http://www.ilias.de/mantis' target='_blank'>http://www.ilias.de/mantis</a>." +
			"<p><b>User Agent</b></p>" +
			navigator.userAgent +
			"<p><b>Error</b></p>";
		estr = estr + ilCOPage.error_str;
		estr = estr + "<p><b>Content</b></p>";
		var content = tinyMCE.get('tinytarget').getContent();
		//content = content.replace(/</g, “&lt;”);
		//content = content.replace(/>/g, “&gt;”);
		content = content.split("<").join("&lt;");
		content = content.split(">").join("&gt;");
		estr = estr + content;
		ediv.innerHTML = "<div style='background-color:#FFFFFF;' id='error_panel'>" +
		"<div style='padding:20px; width:800px; height: 350px; overflow:auto;'>" + estr + "</div></div>";
		ediv.className = "yui-skin-sam";
//		ediv.style.position = 'absolute';
//		ediv.style.width = '700px';
//		ediv.style.height = '350px';
//		ediv.style.overflow = 'auto';
		ediv = mc.appendChild(ediv);
//		var m_el = document.getElementById('iltinymenu');
//		var m_reg = YAHOO.util.Region.getRegion(m_el);
//		YAHOO.util.Dom.setX(ediv, m_reg.x);
//		YAHOO.util.Dom.setY(ediv, m_reg.y + m_reg.height);
		var error_panel = new YAHOO.widget.Panel("error_panel", {
			close: true,
			constraintoviewport:true
		});
		error_panel.render();
		error_panel.moveTo(20, 20);

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
					ilAdvancedSelectionList.selectItem('style_selection', st);
				//}
			}
			
			cnode = cnode.parentNode;
		}
	},
	
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
	
	insertJSAtPlaceholder: function(cmd_id)
	{
		clickcmdid = cmd_id;
		var pl = document.getElementById('CONTENT' + cmd_id);
		pl.style.display = 'none';
		doActionForm('cmd[exec]', 'command', 'insert_par', '', 'PageContent', '');
	}
}

var stopHigh = false;
var Mposx = 0;
var Mposy = 0;
var sel_edit_areas = Array();
var edit_area_class = Array();
var edit_area_original_class = Array();
var openedMenu = "";					// menu currently opened
var current_mouse_over_id;
var cmd_called = false;

ilAddOnLoad(function(){var preloader = new Image();
preloader.src = "./templates/default/images/loader.gif";});
//document.onmousemove=followmouse1;
YAHOO.util.Event.addListener(document, 'mousemove', followmouse1);

/**
* Get inner height of window
*/
function ilGetWinInnerHeight()
{
	if (self.innerHeight)
	{
		return self.innerHeight;
	}
	// IE 6 strict Mode
	else if (document.documentElement && document.documentElement.clientHeight)
	{
		return document.documentElement.clientHeight;
	}
	// other IE
	else if (document.body)
	{
		return document.body.clientHeight;
	}
}

function ilGetWinPageYOffset()
{
	if (typeof(window.pageYOffset ) == 'number')
	{
		return window.pageYOffset;
	}
	else if(document.body && (document.body.scrollLeft || document.body.scrollTop ))
	{
		return document.body.scrollTop;
	}
	else if(document.documentElement && (document.documentElement.scrollLeft || document.documentElement.scrollTop))
	{
		return document.documentElement.scrollTop;
	}
	return 0;
}

function getBodyWidth()
{
	if (document.body && document.body.offsetWidth)
	{
		return document.body.offsetWidth;
	}
	else if (document.documentElement && document.documentElement.offsetWidth)
	{
		return document.documentElement.offsetWidth;
	}
	return 0;
}

function ilGetOffsetTop(el)
{
	var y = 0;
	
	if (typeof(el) == "object" && document.getElementById)
	{
		y = el.offsetTop;
		if (el.offsetParent)
		{
			y += ilGetOffsetTop(el.offsetParent);
		}
		return y;
	}
	else 
	{
		return false;
	}
}

function ilGetMouseX(e)
{
	if (e.pageX)
	{
		return e.pageX;
	}
	else if (document.documentElement)
	{
		return e.clientX + document.documentElement.scrollLeft;
	}
	if (document.body)
	{
		Mposx = e.clientX + document.body.scrollLeft;
	}
}

function ilGetMouseY(e)
{
	if (e.pageY)
	{
		return e.pageY;
	}
	else if (document.documentElement)
	{
		return e.clientY + ilGetWinPageYOffset();
	}
	if (document.body)
	{
		Mposx = e.clientY + document.body.scrollTop;
	}
}

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
			obj.className = mclass;
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
//    if (!e) var e = window.event;
    
//	Mposx = ilGetMouseX(e);
//	Mposy = ilGetMouseY(e);

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
obj.style.visibility = '';
YAHOO.util.Dom.setXY(obj, [x,y], true);

/*	obj.style.visibility = '';
	obj.style.left = x + 10 + "px";
	obj.style.top = y + "px";
	
	var w = Math.floor(getBodyWidth() / 2);
	
	var wih = ilGetWinInnerHeight();
	var yoff = ilGetWinPageYOffset();
	var top = ilGetOffsetTop(obj);
	
	if (Mposx > w)
	{
		obj.style.left = Mposx - (obj.offsetWidth + 10) + "px";
	}

	if (top + (obj.offsetHeight + 10) > wih + yoff)
	{
		obj.style.top = (wih + yoff - (obj.offsetHeight + 10)) + "px";
	}
*/
}

function hideMenu(id, force)
{
	if (cmd_called && (typeof force == 'undefined' || !force)) return;
	obj = document.getElementById(id);
	if (obj)
	{
		obj.style.visibility = 'hidden';
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
/*	if (dragDropShow)
	{
		if(mouseUpBlocked) return;
		mouseUpBlocked = true;
		setTimeout("mouseUpBlocked = false;",200);
		
		// mousebutton released over new object. call moveafter
		//alert(dragId+" - "+overId);
		DID = overId.substr(7);
		OID = dragId.substr(7);
		if (DID != OID) 
		{ 
			doCloseContextMenuCounter = 20;
			openedMenu = "movebeforeaftermenu";
			dd.elements.movebeforeaftermenu.moveTo(Mposx,Mposy);
			dd.elements.movebeforeaftermenu.show();
			cmd1 = 'cmd[exec_'+OID+']';
			cmd2 = 'command'+OID;
			cmd3 = 'moveAfter';
			cmd4 = DID;
			//doActionForm('cmd[exec_'+OID+']','command'+OID+'', 'moveAfter', DID);
		}
	}
*/
	dragId = "";
	mouseIsDown = false;
	dragDropShow = false;
//	dd.elements.dragdropsymbol.hide();
//	dd.elements.dragdropsymbol.moveTo(-1000,-1000);
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
	
	Mposx = ilGetMouseX(e);
	Mposy = ilGetMouseY(e);

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
			showMenu(openedMenu, Mposx, Mposy-10);
		}
		doCloseContextMenuCounter = 20;
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

// try using ed.destroy???
//			tinyMCE.execCommand('mceRemoveControl', false, 'tinytarget');
//			ed.destroy();
//tinyMCE.execCommand('mceAddControl', false, 'tinytarget');
//return;
//			var ta = document.getElementById('tinytarget');
//			var par = ta.parentNode;
//			par.removeChild(ta);
			//pdiv.style.display = '';
			var pdiv = document.getElementById('div_' + ilCOPage.current_td);
			pdiv.style.minHeight = '';
			pdiv.style.minWidth = '';
			moved = true;
		}

		// get placeholder div
		var pdiv = document.getElementById('div_' + div_id);
		var pdiv_reg = YAHOO.util.Region.getRegion(pdiv);
		ilCOPage.current_td = div_id;
//		pdiv.style.minHeight = ilCOPage.minheight + "px";
//		pdiv.style.minWidth = ilCOPage.minwidth + "px";

	}


	// set background "ghost" element
	if (mode == 'td')
	{
		ilCOPage.edit_ghost = "div_" + ilCOPage.current_td;
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
		
		ta_div = YAHOO.util.Dom.insertAfter(ta_div, pdiv);
		ta_div.id = 'tinytarget_div';
		ta_div.style.position = 'absolute';
		ta_div.style.left = '-200px';

	}
//alert("dd");
	// init tiny
	var tinytarget = document.getElementById("tinytarget");
	var show_path = true;
	var resize = true;
	var statusbar = 'bottom';

//show_path = false;
resize = false;
//statusbar = false;

	if (mode == 'td')
	{
		show_path = false;
		resize = false;
		statusbar = false;
	}
show_path = false;
resize = false;
statusbar = false;
	tinytarget.style.display = '';
//alert("bb");
	if (!moved)
	{
		// without using p tags
		/*	remove_linebreaks : false,
			convert_newlines_to_brs : false,
			force_p_newlines : false,
			force_br_newlines : true,
			forced_root_block : 'div', */

		
		tinyMCE.init({
			mode : "textareas",
			theme : "advanced",
			editor_selector : "par_textarea",
			language : "en",
			plugins : "safari,save,paste",
			save_onsavecallback : "saveParagraph",
			fix_list_elements : true,
			theme_advanced_blockformats : "code",
			theme_advanced_toolbar_align : "left",
			theme_advanced_buttons1 : "p,save,b,code,il_strong,styleselect,formatselect,bullist,numlist,outdent,indent,pasteword",
			theme_advanced_buttons2 : "",
			theme_advanced_buttons3 : "",
			content_css: ilCOPage.content_css,
			theme_advanced_toolbar_location : "external",
			theme_advanced_path : show_path,
			theme_advanced_statusbar_location : statusbar,
			valid_elements : "p,br,div[class|id],span[class],code,ul[class],ol[class],li[class]",
			removeformat_selector : 'span,code',
			remove_linebreaks : false,
			convert_newlines_to_brs : false,
			force_p_newlines : false,
			force_br_newlines : true,
			forced_root_block : 'div',
			save_onsavecallback : "saveParagraph",
			theme_advanced_resize_horizontal : false,
			theme_advanced_resizing : resize,
			cleanup_on_startup : true,
			entity_encoding : "raw",
			cleanup: true,

			style_formats : [
				{title : 'Strong', inline : 'span', classes : 'ilc_text_inline_Strong'},
				{title : 'Emph', inline : 'span', classes : 'ilc_text_inline_Emph'},
				{title : 'Important', inline : 'span', classes : 'ilc_text_inline_Important'},
				{title : 'Comment', inline : 'span', classes : 'ilc_text_inline_Comment'},
				{title : 'Quotation', inline : 'span', classes : 'ilc_text_inline_Quotation'},
				{title : 'Accent', inline : 'span', classes : 'ilc_text_inline_Accent'}
			],
			
			paste_auto_cleanup_on_paste : false,
			paste_text_linebreaktype : "br",
			paste_remove_styles: true,

			/**
			 * Event is triggered after the paste plugin put the content
			 * that should be pasted into a dom structure now
			 * BUT the content is not put into the document yet
			 */
			paste_preprocess: function (pl, o) {
				var ed = ed = tinyMCE.activeEditor;
				
				if (o.wordContent)
				{
					o.content = o.content.replace(/(\r\n|\r|\n)/g, '\n');
					o.content = o.content.replace(/(\n)/g, ' ');
				}

				// make all p -> br
				o.content = o.content.replace(/(<p [^>]*>)/g, '');
				o.content = o.content.replace(/(<p>)/g, '');
				o.content = o.content.replace(/(<\/p>)/g, '<br />');
				
				// remove all divs
				o.content = o.content.replace(/(<div [^>]*>)/g, '');
				o.content = o.content.replace(/(<\/div>)/g, '');
			},
			
			/**
			 * Event is triggered after the paste plugin put the content
			 * that should be pasted into a dom structure now
			 * BUT the content is not put into the document yet
			 */
			paste_postprocess: function (pl, o) {
				var ed = ed = tinyMCE.activeEditor;
				
				if (o.wordContent)
				{
					
				}

				// remove all id attributes from the content
				tinyMCE.each(ed.dom.select('*[id!=""]', o.node), function(el) {
					el.id = '';
				});
				ilCOPage.pasting = true;
//				ilCOPage.cleanContent();
			},

			setup : function(ed) {
				ed.onKeyUp.add(function(ed, ev)
				{
//console.log("onKeyPress");
					ilCOPage.autoResize(ed);
				});
				ed.onKeyDown.add(function(ed, ev)
				{
//					console.log("onKeyDown" + ev.keyCode);
//					console.log("shiftKey" + ev.shiftKey);
					if(ev.keyCode == 9 && !ev.shiftKey)
					{
//						console.log("tab");
						YAHOO.util.Event.preventDefault(ev);
						YAHOO.util.Event.stopPropagation(ev);
						ilCOPage.editNextCell();
					}
					if(ev.keyCode == 9 && ev.shiftKey)
					{
//						console.log("backtab");
						YAHOO.util.Event.preventDefault(ev);
						YAHOO.util.Event.stopPropagation(ev);
						ilCOPage.editPreviousCell();
					}
					//console.log("onKeyDown");
				});
				ed.onNodeChange.add(function(ed, cm, n)
				{
//console.log("onNodeChange");

					// clean content after paste
					if (ilCOPage.pasting)
					{
						ilCOPage.pasting = false;
						ilCOPage.cleanContent();
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

				ed.onActivate.add(function(ed, ev)
				{
//					console.log("onActivate");
				});
				ed.onLoadContent.add(function(ed, ev)
				{
//					console.log("onContent");
				});
				ed.onPostProcess.add(function(ed, ev)
				{
//console.log("onPostProcess");
					//ilCOPage.prepareTinyForEditing(true, false);
					//tinyMCE.execCommand('mceFocus',false,'tinytarget');
					//setTimeout('tinyMCE.execCommand(\'mceFocus\',false,\'tinytarget\');', 1);
				});
				ed.onPostRender.add(function(ed, ev)
				{
//console.log("onPostRender");
				});

				ed.onInit.add(function(ed, evt)
				{
//alert("cc");
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
						//alert("ff");
//		console.log("onInit: setContent");
						ed.setContent("<div class='ilc_text_block_Standard'></div>");
						ilCOPage.prepareTinyForEditing(true, false);
						//setTimeout('ilCOPage.prepareTinyForEditing(true);', 1);
						ilCOPage.synchInputRegion();
						ilCOPage.focusTiny();
						cmd_called = false;
					}

					if (mode == 'td')
					{
						ed.setContent(pdiv.innerHTML);
						ilCOPage.prepareTinyForEditing(false, false);
						ilCOPage.synchInputRegion();
						ilCOPage.focusTiny();
						cmd_called = false;
					}
				});
			}

		});
	}
	else
	{
		//prepareTinyForEditing;
		tinyMCE.execCommand('mceToggleEditor', false, 'tinytarget');
		var ed = tinyMCE.get('tinytarget');
		ed.setContent(pdiv.innerHTML);
//		ilCOPage.prepareTinyForEditing(true, false);
		ilCOPage.synchInputRegion();
		ilCOPage.focusTiny();
		cmd_called = false;
	}

	tinyinit = true;
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
		loadergif.src = "./templates/default/images/loader.gif";
		loadergif.border = 0;
		loadergif.style.position = 'absolute';
		ccell.bgColor='';
		ccell.appendChild(loadergif);
	}
    obj.submit();
}

var ccell = null;

function M_in(cell) 
{
	if (cmd_called) return;
    cell.style.cursor='pointer';
    cell.bgColor='#C0C0FF';
    doCloseContextMenuCounter=-1;
    ccell = cell;
}
function M_out(cell) 
{
	if (cmd_called) return;
    cell.bgColor='';
    doCloseContextMenuCounter=5;
    ccell = null;
}

var oldMposx = -1;
var oldMposy = -1;

/*function doKeyDown(e) 
{
    if (!e) var e = window.event;
    kc = e.keyCode;
    kc = kc * 1;

    if(kc == 17) 
	{
		dd.elements.contextmenu.hide();
		oldMposx = Mposx;
		oldMposy = Mposy;
		mouseIsDown = true;
	}
}*/

/*function doKeyUp(e)
{
	if (!e) var e = window.event;
	kc = e.keyCode;
	
	kc = kc*1;
	if(kc==17) 
	{
		mouseIsDown = false;
		dd.elements.dragdropsymbol.hide();
		dd.elements.dragdropsymbol.moveTo(-1000,-1000);
		setTimeout("dragDropShow = false",500);
	}
}*/

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
ilDragContent.prototype.onDragEnter = function(e, id)
{
	target_id = id.substr(6);
	source_id = this.id.substr(7);
	if (source_id != target_id)
	{
		d_target = document.getElementById(id);
		d_target.className = "il_droparea_active";
	}
};

// overwriting onDragDrop function
ilDragContent.prototype.onDragOut = function(e, id)
{
	d_target = document.getElementById(id);
	d_target.className = "il_droparea";
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
	var DOM = tinyMCE.DOM;
	var Event = tinyMCE.dom.Event;
	var e = DOM.get(ed_id + '_external');
	DOM.show(e);

//	DOM.hide(lastExtID);

	var f = Event.add(ed_id + '_external_close', 'click', function() {
		DOM.hide(ed_id + '_external');
		Event.remove(ed_id + '_external_close', 'click', f);
	});

	DOM.show(e);

	if (false)
	{
		DOM.setStyle(e, 'top', 0 - DOM.getRect(ed_id + '_tblext').h - 1);
	}
	else
	{
		// make tinymenu a panel
		var obj = document.getElementById('iltinymenu');
		obj.style.display = "";
		// Create a panel Instance, from the 'resizablepanel' DIV standard module markup
		var menu_panel = new YAHOO.widget.Panel("iltinymenu", {
			draggable: false,
			close: false,
			autofillheight: "body",
			constraintoviewport:false
		});
		menu_panel.render();
		ilCOPage.menu_panel = menu_panel;

		ilCOPage.menu_panel_opened = true;

		DOM.setStyle(e, 'left', -6000);
		var ed_el = document.getElementById(ed_id + '_parent');
		var m_el = document.getElementById('iltinymenu');
//		m_el.style.display = '';
		var ed_reg = YAHOO.util.Region.getRegion(ed_el);
		var m_reg = YAHOO.util.Region.getRegion(m_el);
		var debug = 0;

 //debug = -30;
//		YAHOO.util.Dom.setY(m_el, ed_reg.y - m_reg.height + 1 + debug);
//		YAHOO.util.Dom.setX(m_el, ed_reg.x);
//		menu_panel.moveTo(ed_reg.x,
//			ed_reg.y - m_reg.height + 1 + debug);

		var obj = document.getElementById('iltinymenu_c');
		obj.style.position = 'fixed';
		obj.style.left = '0px';
		obj.style.right = '0px';
		obj.style.top = '0px';

//		menu_panel.moveTo(100, 100);

	}

	// Fixes IE rendering bug
	DOM.hide(e);
	DOM.show(e);
	e.style.filter = '';

//	lastExtID = ed.id + '_external';

	e = null;
};

//ilAddOnLoad(function(){ilCOPage.editTD('cell_0_0');});