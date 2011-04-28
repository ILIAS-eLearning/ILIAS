
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

ilCOPagePres =
{
	init: function ()
	{
		this.initToc();
		this.initInteractiveImages();
	},
	
	//
	// Toc (as used in Wikis)
	//
	
	initToc: function ()
	{
		// init toc
		var cookiePos = document.cookie.indexOf("pg_hidetoc=");
		if (cookiePos > -1 && document.cookie.charAt(cookiePos + 11) == 1)
		{
			this.toggleToc();
		}
	},

	// toggle table of contents
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

	// 
	initInteractiveImages: function ()
	{
		// preload overlay images (necessary?)
		
		// add onmouseover event to all map areas
		$("map.iim > area").mouseover(this.enterIIMArea);
		$("map.iim > area").mouseout(this.leaveIIMArea);
		$("map.iim > area").mouseover(this.overIIMArea);
		$("map.iim > area").mouseout(this.outIIMArea);
	},
	
	// enter a map area: show corresponding images
	overIIMArea: function (e)
	{
//console.log("over");
	},
	
	// enter a map area: show corresponding images
	outIIMArea: function (e)
	{
//console.log("out");
	},
	
	// enter a map area: show corresponding images
	enterIIMArea: function (e)
	{
//console.log("enter");
		var k, tr;
		var t = ilCOPagePres.iim_area[e.target.id].title;
		var iim_id = ilCOPagePres.iim_area[e.target.id].iim_id;
		for (k in ilCOPagePres.iim_trigger)
		{
			tr = ilCOPagePres.iim_trigger[k];
			if (tr.title == t && tr.iim_id == iim_id)
			{
				var base = $("img#base_img_" + tr['tr_id']);
				var pos = base.position();
				var ov = $("img#iim_ov_" + tr['tr_id']);
				var cnt = 1;
				var base_map_name = base.attr('usemap').substr(1);
				
				// display the overlay at the correct position
				ov.css('position', 'absolute');
				ov.css('left', pos.left);
				ov.css('top', pos.top);
//console.log("display overlay");
				ov.css('display', '');

				// copy relevant areas of base map to overlay
//console.log("over");
				if (tr.map == null)
				{
					tr.map = true;
//					$("area#iim_ov_area_" + tr['tr_id']).mouseover(function() {alert("buh!")});
					
					cnt = 1;
					$("map[name='" + base_map_name + "'] > area").each(
						function (i,el) {
							// if title is the same, add area to overlay map
							if (ilCOPagePres.iim_area[el.id]['title'] == t)
							{
//								tr.map.append($(el).clone(false).attr("id", "iim_ov_area_" + tr['tr_id'] + "_" + cnt));
								$("area#iim_ov_area_" + tr['tr_id']).attr("coords", $(el).attr("coords"));
								$("area#iim_ov_area_" + tr['tr_id']).attr("shape", $(el).attr("shape"));
  								$("area#iim_ov_area_" + tr['tr_id']).mouseover(
  									function() {ilCOPagePres.overOvArea(k, true, "iim_ov_" + tr['tr_id'])});
  								$("area#iim_ov_area_" + tr['tr_id']).mouseout(
  									function() {ilCOPagePres.overOvArea(k, false, "iim_ov_" + tr['tr_id'])});
//								ilCOPagePres.overOvArea(k, true);
							}
							cnt++;
						});

					// make the overlay image use the overlay map
//					ov.attr('usemap', "#" + mapname);

				}
				else
				{
//					ilCOPagePres.overOvArea(k, true);
				}
				
			}

		}
	},
	
	// leave a map area: hide corresponding images
	leaveIIMArea: function (e)
	{
//console.log("out");
		var k, tr;
		var t = ilCOPagePres.iim_area[e.target.id].title;
		var iim_id = ilCOPagePres.iim_area[e.target.id].iim_id;
		for (k in ilCOPagePres.iim_trigger)
		{
			tr = ilCOPagePres.iim_trigger[k];
			if (tr.title == t && tr.iim_id == iim_id &&
				(ilCOPagePres.iim_trigger[k]['over_ov_area'] == null ||
					!ilCOPagePres.iim_trigger[k]['over_ov_area']
				))
			{
//console.log("display overlay none");
				$("img#iim_ov_" + tr['tr_id']).css('display', 'none');
			}
		}
	},
	
	overOvArea: function (k, value, ov_id)
	{
//alert("over area " + value);
//console.log("over ov area" + k + ":" + value);
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
	
	addIIMTrigger: function(tr)
	{
		this.iim_trigger[tr.tr_id] = tr;
	},
	
	addIIMArea: function(a)
	{
		this.iim_area[a.area_id] = a;
	}
}
ilAddOnLoad(function() {ilCOPagePres.init();});
