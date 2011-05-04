
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
		var marker_tr_nr = ilCOPagePres.iim_marker[e.target.id].tr_nr;
		var iim_id = ilCOPagePres.iim_marker[e.target.id].iim_id;
		ilCOPagePres.handleOverEvent(iim_id, marker_tr_nr, true);
	},

	/**
	 * Mouse leaves marker -> hide the overlay image 
	 */
	outMarker: function (e)
	{
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
				{"yuicfg":{"visible":false,"fixedcenter":true},
				"auto_hide":false});
		}
		
//console.log("showing trigger " + tr_id);
//console.log("iim_popup_" + tr['iim_id'] + "_" + tr['popup_nr']);
		
		// @todo: show the overlay
		ilOverlay.show(e, "iim_popup_" + tr['iim_id'] + "_" + tr['popup_nr'], null, true, null, null);

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
	}
}
ilAddOnLoad(function() {ilCOPagePres.init();});
