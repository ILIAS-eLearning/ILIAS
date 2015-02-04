
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

il.Overlay = {
	overlays: {},
	cfg: {},
	widthFixed: {},
	trigger: {},
	closeCnt: {},
	closeProcessRunning: {},
	toggle_cl: {},
	waitMouseOut: 2,
	waitAfterClicked: 20,

	add: function (id, cfg) {
		il.Overlay.overlays[id] =
			new YAHOO.widget.Overlay(id, cfg.yuicfg);
		il.Overlay.cfg[id] = cfg;
		il.Overlay.closeCnt[id] = -1;
		$("#" + id).bind("mouseover",
			function (e) {il.Overlay.mouseOver(e, id); });
		$("#" + id).bind("mouseout",
			function (e) {il.Overlay.mouseOut(e, id); });

		// close element
		if (this.getCfg(id, 'close_el') != '') {
			$("#" + this.getCfg(id, 'close_el')).bind("click",
				function (e) {il.Overlay.hide(e, id); });
		}

		if (cfg.trigger) {
			this.addTrigger(cfg.trigger, cfg.trigger_event, id, cfg.anchor_id,
							cfg.fixed_center, 'tl', 'bl');
		}
		il.Overlay.overlays[id].render();
		this.fixPosition(id);
	},

	addTrigger: function (tr_id, tr_ev, ov_id, anchor_id, center, ov_corner, anch_corner) {
		il.Overlay.trigger[tr_id] =
			{trigger_event: tr_ev, overlay_id: ov_id, anchor_id: anchor_id, center: center,
				ov_corner: ov_corner, anch_corner: anch_corner};
		var trigger = document.getElementById(tr_id);

		// added this line instead due to bug 6724
		$("#" + tr_id).unbind(tr_ev);
		$("#" + tr_id).bind(tr_ev,
			function (event) {il.Overlay.togglePerTrigger(event, tr_id); return false; });

	},

	getCfg: function (id, name) {
		if (this.cfg[id] == null || typeof (this.cfg[id]) == 'undefined') {
			return null;
		}
		if (typeof (this.cfg[id][name]) == 'undefined') {
			return null;
		}
		return this.cfg[id][name];
	},

	// toggle overlay by trigger elements (often anchor)
	togglePerTrigger: function (e, tr_id) {
		var ov_id = il.Overlay.trigger[tr_id].overlay_id,
			anchor_id = il.Overlay.trigger[tr_id].anchor_id,
			center = il.Overlay.trigger[tr_id].center,
			ov_corner = il.Overlay.trigger[tr_id].ov_corner,
			anch_corner = il.Overlay.trigger[tr_id].anch_corner;
		this.toggle(e, ov_id, anchor_id, center, ov_corner, anch_corner,
			il.Overlay.trigger[tr_id].trigger_event);
	},

	// toggle overlay	
	toggle: function (e, id, anchor_id, center, ov_corner, anch_corner, tr_ev) {
		if (il.Overlay.overlays[id].cfg.getProperty('visible')) {
			if (tr_ev != "mouseover") {
				this.hide(e, id);
			}
		} else {
			this.show(e, id, anchor_id, center, ov_corner, anch_corner);
		}
	},

	// hide overlay	
	hide: function (e, id) {
		this.overlays[id].hide();
		if (e != null) {
			// bug 9675
			$.event.fix(e).preventDefault();
//			e.preventDefault();
		}
		this.closeCnt[id] = -1;
		this.closeProcessRunning[id] = false;

		var toggle_el = this.getCfg(id, 'toggle_el'),
			toggle_class_on = this.getCfg(id, 'toggle_class_on'),
			toggle_obj;

		if (toggle_el != null && toggle_class_on != null) {
			toggle_obj = document.getElementById(toggle_el);
			if (toggle_obj && this.toggle_cl[toggle_el]) {
				toggle_obj.className = this.toggle_cl[toggle_el];
			}
		}
	},

	// show the overlay
	show: function (e, id, anchor_id, center, ov_corner, anch_corner) {
		var el, toggle_el, toggle_class_on, toggle_obj;

		// hide all other overlays (currently the standard procedure)
		il.Overlay.hideAllOverlays(e, true, id);

		// display the overlay at the anchor position
		el = document.getElementById(id);
		el.style.display = 'block';
		el.style.zIndex = "1200";
		if (anchor_id != null && anchor_id != '') {
			this.overlays[id].cfg.setProperty("context", [anchor_id, ov_corner, anch_corner]);
			this.overlays[id].cfg.setProperty("fixedcenter", false);
		} else if (center) {
			this.overlays[id].cfg.setProperty("fixedcenter", true);
		}
		this.overlays[id].show();
		this.fixPosition(id);

		// invoke close process (if only the anchor is clicked,
		// the overlay will be hidden after some time, mouseover on the overlay will prevent this)
		if (this.getCfg(id, 'auto_hide')) {
			this.closeCnt[id] = this.waitAfterClicked;
			this.closeProcess(id);
		}

		// should an additional element be toggled (style class)
		toggle_el = this.getCfg(id, 'toggle_el');
		toggle_class_on = this.getCfg(id, 'toggle_class_on');

		if (toggle_el != null && toggle_class_on != null) {
			toggle_obj = document.getElementById(toggle_el);
			if (toggle_obj) {
				this.toggle_cl[toggle_el] = toggle_obj.className;
				toggle_obj.className = toggle_class_on;
			}
		}

		// get content asynchronously
		if (this.getCfg(id, 'asynch')) {
			this.loadAsynch(id, this.getCfg(id, 'asynch_url'));
		}

		// handle event
		if (e) {
			e = $.event.fix(e);
			e.preventDefault();
			e.stopPropagation();
		}
	},

	fixPosition: function (id) {
		var el = document.getElementById(id),
			el_reg,
			cl, cl_reg,
			newHeight,
			newy;

		if (!el) {
			return;
		}
		el.style.overflow = '';
		el_reg = il.Util.getRegion(el);
		cl_reg = il.Util.getViewportRegion();
		cl = document.getElementById("fixed_content");
		if (cl && $(el).closest(cl).length) {
			cl_reg = il.Util.getRegion(cl);
		}
		
		
		// make it smaller, if window height is not sufficient
		if (cl_reg.height < el_reg.height + 40) {
			newHeight = cl_reg.height - 40;
			if (newHeight < 150) {
				newHeight = 150;
			}
			el.style.height = newHeight + "px";
			if (!this.widthFixed[id]) {
				el.style.width = el_reg.width + 20 + "px";
				this.widthFixed[id] = true;
			}
			el_reg = il.Util.getRegion(el);
		}

		// to low -> show it higher
		if (cl_reg.bottom - 20 < el_reg.bottom) {
			newy = el_reg.y - (el_reg.bottom - cl_reg.bottom + 20);
			if (newy < cl_reg.top) {
				newy = cl_reg.top;
			}
			this.setY(id, newy);
			el_reg = il.Util.getRegion(el);
		}

		// to far to the right -> show it more to the left
		if (cl_reg.right - 20 < el_reg.right) {
			this.setX(id, el_reg.x - (el_reg.right - cl_reg.right + 20));
		}

		el.style.overflow = 'auto';
	},

	/**
	 * Set width of an overlay
	 */
	setWidth: function (id, w) {
		var el = document.getElementById(id);
		el.style.width = w + "px";
	},

	/**
	 * Set height of an overlay
	 */
	setHeight: function (id, h) {
		var el = document.getElementById(id);
		el.style.height = h + "px";
	},

	/**
	 * Set x
	 */
	setX: function (id, x) {
		$("#" + id).offset({top: $("#" + id).offset().top, left: x});
	},

	/**
	 * Set y
	 */
	setY: function (id, y) {
		$("#" + id).offset({top: y, left: $("#" + id).offset().left});
	},

	// hide all overlays
	hideAllOverlays: function (e, force, omit) {
		var k, isIn, tgt, el, el_reg;

		for (k in il.Overlay.overlays) {
			isIn = false;

			if (k == omit) {
				continue;
			}

			// problems with form select: pageXY can be outside layer
			if (!force) {
				try {
					tgt = e.target;
					// #13209 - IE11 select options do not have offsetParent
					if (tgt.offsetParent === null) {						
						tgt = tgt.parentNode;
					}
					if (tgt.offsetParent.id == k) {
						isIn = true;
					}
				} catch (err) {
				}
			}

			// try with event coordiantes
			if (!force && !isIn) {
				el = document.getElementById(k);
				if (el != null) {
					if (il.Util.coordsInElement(e.pageX, e.pageY, el)) {
						isIn = true;
					}
				}
			}

			if (!isIn) {
				if (k != 'ilHelpPanel') {
					il.Overlay.hide(null, k);
				}
			}
		}
	},

	mouseOver: function (e, id) {
		this.closeCnt[id] = -1;
		//console.log("mouseOver");
	},

	mouseOut: function (e, id) {
		if (this.getCfg(id, 'auto_hide')) {
			this.closeCnt[id] = this.waitMouseOut;
			if (!this.closeProcessRunning[id]) {
				this.closeProcess(id);
			}
		}
	},

	closeProcess: function (id) {
		if (this.closeCnt[id] > -1) {
			this.closeCnt[id]--;
			if (this.closeCnt[id] == 0) {
				this.hide(null, id);
			}
		}
		if (this.closeCnt[id] > -1) {
			setTimeout("il.Overlay.closeProcess('" + id + "')", 200);
			this.closeProcessRunning[id] = true;
		} else {
			this.closeProcessRunning[id] = false;
		}
	},

	loadAsynch: function (id, sUrl) {
		il.Util.ajaxReplaceInner(sUrl, id);
		return false;
	},
	
	subscribe: function (id, ev, func) {
		il.Overlay.overlays[id].subscribe(ev, func);
	}
};

$(document).bind("click",
	function (e) {il.Overlay.hideAllOverlays(e, false, ""); });
