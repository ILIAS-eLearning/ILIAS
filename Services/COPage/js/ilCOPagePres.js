
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

ilCOPagePres =
{
	/**
	 * Basic init function
	 */
	init: function ()
	{
		this.initToc();
		this.initInteractiveImages();
	},
	
	//
	// Toc (as used in Wikis)
	//
	
	/**
	 * Init the table of content
	 */
	initToc: function ()
	{
		// init toc
		var cookiePos = document.cookie.indexOf("pg_hidetoc=");
		if (cookiePos > -1 && document.cookie.charAt(cookiePos + 11) == 1)
		{
			this.toggleToc();
		}
	},

	/**
	 * Toggle the table of content
	 */
	toggleToc: function()
	{
		var toc = document.getElementById('ilPageTocContent');

		if (!toc)
		{
			return;
		}
		var toc_on = document.getElementById('ilPageTocOn');
		var toc_off = document.getElementById('ilPageTocOff');
		if (toc && toc.style.display == 'none')
		{
			toc.style.display = 'block';
			toc_on.style.display = 'none';
			toc_off.style.display = '';
			document.cookie = "pg_hidetoc=0";
		}
		else
		{
			toc_on.style.display = '';
			toc_off.style.display = 'none';
			toc.style.display = 'none';
			document.cookie = "pg_hidetoc=1";
		}
	},
	
	//
	// Interactive Images
	//

	iim_trigger: {},
	iim_area: {},
	iim_popup: {},
	iim_marker: {},
	dragging: false,

	/**
	 * Init interactive images
	 */
	initInteractiveImages: function ()
	{
		// preload overlay images (necessary?)
		
		// add onmouseover event to all map areas
		$("map.iim > area").mouseover(this.overBaseArea);
		$("map.iim > area").mouseout(this.outBaseArea);
		$("map.iim > area").click(this.clickBaseArea);
		
		$("a.ilc_marker_Marker").mouseover(this.overMarker);
		$("a.ilc_marker_Marker").mouseout(this.outMarker);
		$("a.ilc_marker_Marker").click(this.clickMarker);

	},
	
	/**
	 * Mouse over marker -> show the overlay image
	 */
	overMarker: function (e)
	{
		if (this.dragging)
		{
			return;
		}
		
		var marker_tr_nr = ilCOPagePres.iim_marker[e.target.id].tr_nr;
		var iim_id = ilCOPagePres.iim_marker[e.target.id].iim_id;
		ilCOPagePres.handleOverEvent(iim_id, marker_tr_nr, true);
	},

	/**
	 * Mouse leaves marker -> hide the overlay image 
	 */
	outMarker: function (e)
	{
		if (this.dragging)
		{
			return;
		}

		var marker_tr_nr = ilCOPagePres.iim_marker[e.target.id].tr_nr;
		var iim_id = ilCOPagePres.iim_marker[e.target.id].iim_id;
		ilCOPagePres.handleOutEvent(iim_id, marker_tr_nr, true);
	},

	/**
	 * Mouse over base image map area -> show the overlay image
	 * and (on first time) init the image map of the overlay image
	 */
	overBaseArea: function (e)
	{
		var area_tr_nr = ilCOPagePres.iim_area[e.target.id].tr_nr;
		var iim_id = ilCOPagePres.iim_area[e.target.id].iim_id;
		
		ilCOPagePres.handleOverEvent(iim_id, area_tr_nr, false);
	},
	
	/**
	 * Mouse over base image map area or marker -> show the overlay image
	 * and (on first time) init the image map of the overlay image
	 */
	handleOverEvent: function (iim_id, area_tr_nr, is_marker)
	{
//console.log("over enter");
		var k, j, tr, coords, ovx, ovy;
		
		if (this.dragging)
		{
			return;
		}

		for (k in ilCOPagePres.iim_trigger)
		{
			tr = ilCOPagePres.iim_trigger[k];
			if (tr.nr == area_tr_nr && tr.iim_id == iim_id)
			{
				var base = $("img#base_img_" + tr.iim_id);
//console.log("get base" + tr['tr_id']);
				var pos = base.position();
				var ov = $("img#iim_ov_" + tr['tr_id']);
//console.log("get iim_ov_" + tr['tr_id']);
				var cnt = 1;
				var base_map_name = base.attr('usemap').substr(1);
				
				// display the overlay at the correct position
				ov.css('position', 'absolute');
				ovx = parseInt(tr['ovx']);
				ovy = parseInt(tr['ovy']);
				ov.css('left', pos.left + ovx);
				ov.css('top', pos.top + ovy);
				ov.css('display', '');

				// on first time we need to initialize the
				// image map of the overlay image
				if (tr.map_initialized == null && !is_marker)
				{
					tr.map_initialized = true;
//console.log(tr);
					cnt = 1;
					$("map[name='" + base_map_name + "'] > area").each(
						function (i,el) {
							// if title is the same, add area to overlay map
							if (ilCOPagePres.iim_area[el.id]['tr_nr'] == area_tr_nr)
							{
								coords = $(el).attr("coords");
								// fix coords
								switch($(el).attr("shape").toLowerCase())
								{
									case "rect":
										var c = coords.split(",");
										coords = "" + (parseInt(c[0]) - ovx) + "," +
											(parseInt(c[1]) - ovy) + "," +
											(parseInt(c[2]) - ovx) + "," +
											(parseInt(c[3]) - ovy);
										break;
										
									case "poly":
										var c = coords.split(",");
										coords = "";
										var sep = "";
										for (j in c)
										{
											if (j % 2 == 0)
											{
												coords = coords + sep + parseInt(c[j] - ovx);
											}
											else
											{
												coords = coords + sep + parseInt(c[j] - ovy);
											}
											sep = ",";
										}
										break;
										
									case "circle":
										var c = coords.split(",");
										coords = "" + (parseInt(c[0]) - ovx) + "," +
											(parseInt(c[1]) - ovy) + "," +
											(parseInt(c[2]));
										break;
								}
								
								// set shape and coords
								$("area#iim_ov_area_" + tr['tr_id']).attr("coords", coords);
								$("area#iim_ov_area_" + tr['tr_id']).attr("shape", $(el).attr("shape"));
								
								// add mouse event listeners
								var k2 = k;
								var i2 = "iim_ov_" + tr['tr_id'];
								var tr2 = tr['tr_id'];
  								$("area#iim_ov_area_" + tr['tr_id']).mouseover(
  									function() {ilCOPagePres.overOvArea(k2, true, i2)});
  								$("area#iim_ov_area_" + tr['tr_id']).mouseout(
  									function() {ilCOPagePres.overOvArea(k2, false, i2)});
  								$("area#iim_ov_area_" + tr['tr_id']).click(
  									function(e) {ilCOPagePres.clickOvArea(e, tr2)});
							}
							cnt++;
						});
				}
			}
		}
	},

	/**
	 * Leave a base image map area: hide corresponding images
	 */
	outBaseArea: function (e)
	{
		var area_tr_nr = ilCOPagePres.iim_area[e.target.id].tr_nr;
		var iim_id = ilCOPagePres.iim_area[e.target.id].iim_id;
		ilCOPagePres.handleOutEvent(iim_id, area_tr_nr, false);
	},
	
	/**
	 * Leave a base image map area: hide corresponding images
	 */
	handleOutEvent: function (iim_id, area_tr_nr, is_marker)
	{
//console.log("out");
		var k, tr;
		
		if (this.dragging)
		{
			return;
		}
		
		for (k in ilCOPagePres.iim_trigger)
		{
			tr = ilCOPagePres.iim_trigger[k];
			if (tr.nr == area_tr_nr && tr.iim_id == iim_id &&
				(ilCOPagePres.iim_trigger[k]['over_ov_area'] == null ||
					!ilCOPagePres.iim_trigger[k]['over_ov_area']
				))
			{
				$("img#iim_ov_" + tr['tr_id']).css('display', 'none');
			}
		}
	},
	
	
	
	/**
	 * Triggered by mouseover/out on imagemap of overlay image
	 */
	overOvArea: function (k, value, ov_id)
	{
		if (this.dragging)
		{
			return;
		}

//console.log("overOvArea " + k + ":" + ov_id);
		ilCOPagePres.iim_trigger[k]['over_ov_area'] = value;
		if (value)
		{
			$("img#" + ov_id).css('display', '');
		}
		else
		{
			$("img#" + ov_id).css('display', 'none');
		}
	},
	
	/**
	 * A marker is clicked
	 */
	clickMarker: function (e)
	{
		var k;
		var marker_tr_nr = ilCOPagePres.iim_marker[e.target.id].tr_nr;
		var iim_id = ilCOPagePres.iim_marker[e.target.id].iim_id;

		if (this.dragging)
		{
			return;
		}

		// iterate through the triggers and search the correct one
		for (k in ilCOPagePres.iim_trigger)
		{
			tr = ilCOPagePres.iim_trigger[k];
			if (tr.nr == marker_tr_nr && tr.iim_id == iim_id)
			{
				ilCOPagePres.handleAreaClick(e, tr['tr_id']);
			}
		}
	},

	/**
	 * A base image map area is clicked
	 */
	clickBaseArea: function (e)
	{
		var k;
		var area_tr_nr = ilCOPagePres.iim_area[e.target.id].tr_nr;
		var iim_id = ilCOPagePres.iim_area[e.target.id].iim_id;

		if (this.dragging)
		{
			return;
		}

		// iterate through the triggers and search the correct one
		for (k in ilCOPagePres.iim_trigger)
		{
			tr = ilCOPagePres.iim_trigger[k];
			if (tr.nr == area_tr_nr && tr.iim_id == iim_id)
			{
				ilCOPagePres.handleAreaClick(e, tr['tr_id']);
			}
		}
	},
	
	/**
	 * Handle area click (triggered by base or overlay image map area)
	 */
	handleAreaClick: function (e, tr_id)
	{
		var tr = ilCOPagePres.iim_trigger[tr_id];
		var el = document.getElementById("iim_popup_" + tr['iim_id'] + "_" + tr['popup_nr']);
		
		if (el == null || this.dragging)
		{
			e.preventDefault();
			return;
		}
		
		// on first time we need to initialize content overlay
		if (tr.popup_initialized == null)
		{
			tr.popup_initialized = true;
			
			// @todo: initialize the overlay
			/*
			ilOverlay.add("iim_popup_" + tr.tr_id,
				{"yuicfg":{"visible":false,"fixedcenter":false,
					"context":["iim_ov_area_" + tr.tr_id,"tl","bl",["beforeShow","windowResize"]]},
				"trigger":"iim_ov_area_" + tr.tr_id,
				"trigger_event":"click",
				"anchor_id":"iim_ov_area_" + tr.tr_id,
				"auto_hide":false,
				"close_el":"iim_ov_area_" + tr.tr_id});
			*/
			ilOverlay.add("iim_popup_" + tr['iim_id'] + "_" + tr['popup_nr'],
				{"yuicfg":{"visible":false,"fixedcenter":false},
				"auto_hide":false});
		}
		
//console.log("showing trigger " + tr_id);
//console.log("iim_popup_" + tr['iim_id'] + "_" + tr['popup_nr']);
		
		// show the overlay
		var base = $("img#base_img_" + ilCOPagePres.iim_trigger[tr_id]['iim_id']);
		var pos = base.position();
		var x = pos.left + parseInt(ilCOPagePres.iim_trigger[tr_id]['popx']);
		var y = pos.top + parseInt(ilCOPagePres.iim_trigger[tr_id]['popy']);
		ilOverlay.setWidth("iim_popup_" + tr['iim_id'] + "_" + tr['popup_nr'], ilCOPagePres.iim_trigger[tr_id]['popwidth']);
		ilOverlay.setHeight("iim_popup_" + tr['iim_id'] + "_" + tr['popup_nr'], ilCOPagePres.iim_trigger[tr_id]['popheight']);
		ilOverlay.toggle(e, "iim_popup_" + tr['iim_id'] + "_" + tr['popup_nr'], null, false, null, null, "click");
		ilOverlay.setX("iim_popup_" + tr['iim_id'] + "_" + tr['popup_nr'], x);
		ilOverlay.setY("iim_popup_" + tr['iim_id'] + "_" + tr['popup_nr'], y);

		e.preventDefault();
	},
	
	/**
	 * A overlay image map area is clicked
	 */
	clickOvArea: function (e, tr_id)
	{
		ilCOPagePres.handleAreaClick(e, tr_id);
	},

	addIIMTrigger: function(tr)
	{
//console.log(tr);
		this.iim_trigger[tr.tr_id] = tr;
	},
	
	addIIMArea: function(a)
	{
//console.log(a);
		this.iim_area[a.area_id] = a;
	},
	
	addIIMPopup: function(p)
	{
		this.iim_popup[p.pop_id] = p;
	},
	
	addIIMMarker: function(m)
	{
		this.iim_marker[m.m_id] = m;
		var base = $("img#base_img_" + m.iim_id);
		var pos = base.position();
		var mark = $("a#" + m['m_id']);
		// display the marker at the correct position
		mark.css('position', 'absolute');
		mx = parseInt(m['markx']);
		my = parseInt(m['marky']);
		mark.css('left', pos.left + mx);
		mark.css('top', pos.top + my);
		mark.css('display', '');
	},
	
	/**
	 * Make marker draggable
	 */
	startDraggingMarker: function(tr_nr)
	{
		this.dragging = true;
		for (k in ilCOPagePres.iim_marker)
		{
			if (ilCOPagePres.iim_marker[k]['tr_nr'] == tr_nr)
			{
				var mark = ilCOPagePres.iim_marker[k];
				$("a#" + ilCOPagePres.iim_marker[k]['m_id']).css("display", "");
				$("a#" + ilCOPagePres.iim_marker[k]['m_id']).draggable({
					drag: function(event, ui) {
						var base = $("img#base_img_" + mark.iim_id);
						var bpos = base.position();
						var marker = $("a#" + mark.m_id);
						var mpos = marker.position();
						var position = (Math.round(mpos.left) - Math.round(bpos.left)) + "," +
							(Math.round(mpos.top) - Math.round(bpos.top));
						$("input#markpos_" + mark.tr_nr).attr("value", position);
					}
				});
				
				ilCOPagePres.initDragToolbar();
			}
			else
			{
				$("a#" + ilCOPagePres.iim_marker[k]['m_id']).css("display", "none");
			}
		}
	},
	
	stopDraggingMarker: function()
	{
		this.dragging = false;
	},
	
	/**
	 * Make overlay draggable
	 */
	startDraggingOverlay: function(tr_nr)
	{
		this.dragging = true;

		for (k in ilCOPagePres.iim_trigger)
		{
			var trigger = ilCOPagePres.iim_trigger[k];
			if (trigger['nr'] == tr_nr)
			{
				var dtr = trigger;
				var ov = $("img#iim_ov_" + dtr['tr_id']);
				var base = $("img#base_img_" + dtr.iim_id);
				var bpos = base.position();
				
				ovx = parseInt(dtr['ovx']);
				ovy = parseInt(dtr['ovy']);
				ov.css('left', bpos.left + ovx);
				ov.css('top', bpos.top + ovy);
				ov.css('display', '');
				ov.css("position", "absolute");

				var dtr = trigger;
				ov.draggable({
					stop: function(event, ui) {
						var ovpos = ov.position();
						var position = (Math.round(ovpos.left) - Math.round(bpos.left)) + "," +
							(Math.round(ovpos.top) - Math.round(bpos.top));

						$("input#ovpos_" + dtr.nr).attr("value", position);
					}
				});
				
				ilCOPagePres.initDragToolbar();
			}
			else
			{
//				$("img#iim_ov_" + trigger['tr_id']).css("display", "none");
			}
		}
	},
	
	/**
	 * Make popup draggable
	 */
	startDraggingPopup: function(tr_nr)
	{
		var i, k;
		this.dragging = true;

		// get correct trigger
		for (k in ilCOPagePres.iim_trigger)
		{
			if (ilCOPagePres.iim_trigger[k]['nr'] == tr_nr)
			{
				var dtr = ilCOPagePres.iim_trigger[k];
				
				// get correct popup
				for (i in ilCOPagePres.iim_popup)
				{
					if (ilCOPagePres.iim_popup[i]['nr'] == 
						ilCOPagePres.iim_trigger[k]['popup_nr'])
					{
						var cpop = ilCOPagePres.iim_popup[i];
						var pdummy = document.getElementById("popupdummy");
						if (pdummy == null)
						{
							$('div#il_center_col').append('<div id="popupdummy" class="ilc_iim_ContentPopup"></div>');
							pdummy = $("div#popupdummy");
						}
						else
						{
							pdummy = $("div#popupdummy");
						}
						var base = $("img#base_img_" + cpop.iim_id);
						var bpos = base.position();
//console.log(dtr);
						popx = parseInt(dtr['popx']);
						popy = parseInt(dtr['popy']);
						pdummy.css("position", "absolute");
						pdummy.css('left', bpos.left + popx);
						pdummy.css('top', bpos.top + popy);
						pdummy.css('width', dtr['popwidth']);
						pdummy.css('height', dtr['popheight']);
						pdummy.css('display', '');
						
						pdummy.draggable({
							stop: function(event, ui) {
								var pdpos = pdummy.position();
								var position = (Math.round(pdpos.left) - Math.round(bpos.left)) + "," +
									(Math.round(pdpos.top) - Math.round(bpos.top));
								$("input#poppos_" + dtr.nr).attr("value", position);
							}
						});
						ilCOPagePres.initDragToolbar();
					}
					else
					{
		//				$("img#iim_ov_" + trigger['tr_id']).css("display", "none");
					}
				}
			}
		}
	},

	/**
	 * Init drag toolbar
	 */
	initDragToolbar: function(tr_nr)
	{
		// show the toolbar
		$("div#drag_toolbar").css("display", "");
		$("a#save_pos_button").click(function () {
			$("input#update_tr_button").trigger("click");
			});
	}

}
ilAddOnLoad(function() {ilCOPagePres.init();});
