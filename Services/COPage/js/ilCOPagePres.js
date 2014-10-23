
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

il.COPagePres =
{
	/**
	 * Basic init function
	 */
	init: function () {
		this.initToc();
		this.initInteractiveImages();
		this.updateQuestionOverviews();
		this.initMapAreas();
		this.initAdvancedContent();
		this.initAudioVideo();
	},
	
	//
	// Toc (as used in Wikis)
	//
	
	/**
	 * Init the table of content
	 */
	initToc: function () {
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
	toggleToc: function() {
		var toc_on, toc_off, toc = document.getElementById('ilPageTocContent');

		if (!toc) {
			return;
		}

		toc_on = document.getElementById('ilPageTocOn');
		toc_off = document.getElementById('ilPageTocOff');

		if (toc && toc.style.display == 'none') {
			toc.style.display = 'block';
			toc_on.style.display = 'none';
			toc_off.style.display = '';
			document.cookie = "pg_hidetoc=0";
		} else {
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
	initInteractiveImages: function () {
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
	overMarker: function (e) {
		var marker_tr_nr, iim_id;

		if (this.dragging) {
			return;
		}
		
		marker_tr_nr = il.COPagePres.iim_marker[e.target.id].tr_nr;
		iim_id = il.COPagePres.iim_marker[e.target.id].iim_id;
		il.COPagePres.handleOverEvent(iim_id, marker_tr_nr, true);
	},

	/**
	 * Mouse leaves marker -> hide the overlay image 
	 */
	outMarker: function (e) {
		var marker_tr_nr, iim_id;
		if (this.dragging) {
			return;
		}

		marker_tr_nr = il.COPagePres.iim_marker[e.target.id].tr_nr;
		iim_id = il.COPagePres.iim_marker[e.target.id].iim_id;
		il.COPagePres.handleOutEvent(iim_id, marker_tr_nr);
	},

	/**
	 * Mouse over base image map area -> show the overlay image
	 * and (on first time) init the image map of the overlay image
	 */
	overBaseArea: function (e) {
		var area_tr_nr = il.COPagePres.iim_area[e.target.id].tr_nr,
			iim_id = il.COPagePres.iim_area[e.target.id].iim_id;

		il.COPagePres.handleOverEvent(iim_id, area_tr_nr, false);
	},
	
	/**
	 * Mouse over base image map area or marker -> show the overlay image
	 * and (on first time) init the image map of the overlay image
	 */
	handleOverEvent: function (iim_id, area_tr_nr, is_marker)
	{
//console.log("over enter");
		var k, j, tr, coords, ovx, ovy, base, ov, base_map_name, c, k2, i2, tr2;
		
		if (this.dragging) {
			return;
		}

		for (k in il.COPagePres.iim_trigger) {
			tr = il.COPagePres.iim_trigger[k];

			if (tr.nr == area_tr_nr && tr.iim_id == iim_id) {
				base = $("img#base_img_" + tr.iim_id);
				ov = $("img#iim_ov_" + tr.tr_id);
				// no overlay image? -> skip
				if (ov.length == 0) {
					continue;
				}
				
				// no usamap (e.g. edit mode) -> skip)
				if (typeof(base.attr('usemap')) === "undefined") {
					continue;
				}
				
				base_map_name = base.attr('usemap').substr(1);
				
				// display the overlay at the correct position
				ov.css('position', 'absolute');
				ovx = parseInt(tr.ovx, 10);
				ovy = parseInt(tr.ovy, 10);
				ov.css('display', '');

				// this fixes the position in case of the toc2win
				// view, if the fixed div has been scrolled
				$(ov).position({
					my: "left top",
					at: "left+" + ovx + " top+" + ovy,
					of: "img#base_img_" + tr.iim_id,
					collision: "none"
				});

				// on first time we need to initialize the
				// image map of the overlay image
				if (tr.map_initialized == null && !is_marker)
				{
					tr.map_initialized = true;
//console.log(tr);
					$("map[name='" + base_map_name + "'] > area").each(
						function (i,el) {
							// if title is the same, add area to overlay map
							if (il.COPagePres.iim_area[el.id].tr_nr == area_tr_nr) {
								coords = $(el).attr("coords");
								// fix coords
								switch($(el).attr("shape").toLowerCase()) {

									case "rect":
										c = coords.split(",");
										coords = String((parseInt(c[0], 10) - ovx) + "," +
											(parseInt(c[1], 10) - ovy) + "," +
											(parseInt(c[2], 10) - ovx) + "," +
											(parseInt(c[3], 10) - ovy));
										break;
										
									case "poly":
										c = coords.split(",");
										coords = "";
										var sep = "";
										for (j in c) {
											if (j % 2 == 0) {
												coords = coords + sep + parseInt(c[j] - ovx, 10);
											} else {
												coords = coords + sep + parseInt(c[j] - ovy, 10);
											}
											sep = ",";
										}
										break;
										
									case "circle":
										c = coords.split(",");
										coords = String((parseInt(c[0], 10) - ovx) + "," +
											(parseInt(c[1], 10) - ovy) + "," +
											(parseInt(c[2], 10)));
										break;
								}
								
								// set shape and coords
								$("area#iim_ov_area_" + tr.tr_id).attr("coords", coords);
								$("area#iim_ov_area_" + tr.tr_id).attr("shape", $(el).attr("shape"));
								
								// add mouse event listeners
								k2 = k;
								i2 = "iim_ov_" + tr.tr_id;
								tr2 = tr.tr_id;
  								$("area#iim_ov_area_" + tr.tr_id).mouseover(
  									function() {il.COPagePres.overOvArea(k2, true, i2);});
  								$("area#iim_ov_area_" + tr.tr_id).mouseout(
  									function() {il.COPagePres.overOvArea(k2, false, i2);});
  								$("area#iim_ov_area_" + tr.tr_id).click(
  									function(e) {il.COPagePres.clickOvArea(e, tr2);});
							}
						});
				}
			}
		}
	},

	/**
	 * Leave a base image map area: hide corresponding images
	 */
	outBaseArea: function (e) {
		var area_tr_nr = il.COPagePres.iim_area[e.target.id].tr_nr,
			iim_id = il.COPagePres.iim_area[e.target.id].iim_id;
		il.COPagePres.handleOutEvent(iim_id, area_tr_nr);
	},
	
	/**
	 * Leave a base image map area: hide corresponding images
	 */
	handleOutEvent: function (iim_id, area_tr_nr)
	{
//console.log("out");
		var k, tr;
		
		if (this.dragging) {
			return;
		}
		
		for (k in il.COPagePres.iim_trigger) {
			tr = il.COPagePres.iim_trigger[k];
			if (tr.nr == area_tr_nr && tr.iim_id == iim_id &&
				(il.COPagePres.iim_trigger[k].over_ov_area == null ||
					!il.COPagePres.iim_trigger[k].over_ov_area
				)) {
				$("img#iim_ov_" + tr.tr_id).css('display', 'none');
			}
		}
	},

	
	/**
	 * Triggered by mouseover/out on imagemap of overlay image
	 */
	overOvArea: function (k, value, ov_id) {
		if (this.dragging) {
			return;
		}

//console.log("overOvArea " + k + ":" + ov_id);
		il.COPagePres.iim_trigger[k].over_ov_area = value;
		if (value) {
			$("img#" + ov_id).css('display', '');
		} else {
			$("img#" + ov_id).css('display', 'none');
		}
	},
	
	/**
	 * A marker is clicked
	 */
	clickMarker: function (e)
	{
		var k, tr,
			marker_tr_nr = il.COPagePres.iim_marker[e.target.id].tr_nr,
			iim_id = il.COPagePres.iim_marker[e.target.id].iim_id;

		if (il.COPagePres.iim_marker[e.target.id].edit_mode == "1") {
			return;
		}
		
		if (this.dragging) {
			return;
		}

		// iterate through the triggers and search the correct one
		for (k in il.COPagePres.iim_trigger) {
			tr = il.COPagePres.iim_trigger[k];
			if (tr.nr == marker_tr_nr && tr.iim_id == iim_id) {
				il.COPagePres.handleAreaClick(e, tr.tr_id);
			}
		}
	},

	/**
	 * A base image map area is clicked
	 */
	clickBaseArea: function (e) {
		var k, tr,
			area_tr_nr = il.COPagePres.iim_area[e.target.id].tr_nr,
			iim_id = il.COPagePres.iim_area[e.target.id].iim_id;

		if (this.dragging) {
			return;
		}

		// iterate through the triggers and search the correct one
		for (k in il.COPagePres.iim_trigger) {
			tr = il.COPagePres.iim_trigger[k];
			if (tr.nr == area_tr_nr && tr.iim_id == iim_id) {
				il.COPagePres.handleAreaClick(e, tr.tr_id);
			}
		}
	},
	
	/**
	 * Handle area click (triggered by base or overlay image map area)
	 */
	handleAreaClick: function (e, tr_id) {
		var tr = il.COPagePres.iim_trigger[tr_id],
			el = document.getElementById("iim_popup_" + tr.iim_id + "_" + tr.popup_nr),
			base, pos, x, y;
		
		if (el == null || this.dragging) {
			e.preventDefault();
			return;
		}
		
		// on first time we need to initialize content overlay
		if (tr.popup_initialized == null) {
			tr.popup_initialized = true;
			
			il.Overlay.add("iim_popup_" + tr.iim_id + "_" + tr.popup_nr,
				{"yuicfg":{"visible":false,"fixedcenter":false},
				"auto_hide":false});
		}
		
//console.log("showing trigger " + tr_id);
//console.log("iim_popup_" + tr['iim_id'] + "_" + tr['popup_nr']);
		
		// show the overlay
		base = $("img#base_img_" + il.COPagePres.iim_trigger[tr_id].iim_id);
		pos = base.offset();
		x = pos.left + parseInt(il.COPagePres.iim_trigger[tr_id].popx, 10);
		y = pos.top + parseInt(il.COPagePres.iim_trigger[tr_id].popy, 10);
		il.Overlay.setWidth("iim_popup_" + tr.iim_id + "_" + tr.popup_nr, il.COPagePres.iim_trigger[tr_id].popwidth);
		il.Overlay.setHeight("iim_popup_" + tr.iim_id + "_" + tr.popup_nr, il.COPagePres.iim_trigger[tr_id].popheight);
		il.Overlay.toggle(e, "iim_popup_" + tr.iim_id + "_" + tr.popup_nr, null, false, null, null, "click");
		il.Overlay.setX("iim_popup_" + tr.iim_id + "_" + tr.popup_nr, x);
		il.Overlay.setY("iim_popup_" + tr.iim_id + "_" + tr.popup_nr, y);

		e.preventDefault();
	},
	
	/**
	 * A overlay image map area is clicked
	 */
	clickOvArea: function (e, tr_id){
		il.COPagePres.handleAreaClick(e, tr_id);
	},

	addIIMTrigger: function(tr) {
//console.log(tr);
		this.iim_trigger[tr.tr_id] = tr;
	},
	
	addIIMArea: function(a) {
//console.log(a);
		this.iim_area[a.area_id] = a;
	},
	
	addIIMPopup: function(p) {
		this.iim_popup[p.pop_id] = p;
	},
	
	addIIMMarker: function(m) {
		var base, pos, mark, mx, my;

		this.iim_marker[m.m_id] = m;
		base = $("img#base_img_" + m.iim_id);
		pos = base.position();
		mark = $("a#" + m.m_id);
		// display the marker at the correct position
		mark.css('position', 'absolute');
		mx = parseInt(m.markx, 10);
		my = parseInt(m.marky, 10);
		mark.css('left', pos.left + mx + $("#fixed_content").scrollLeft());
		mark.css('top', pos.top + my + $("#fixed_content").scrollTop());
		mark.css('display', '');
	},
	
	fixMarkerPositions: function () {
		var m, k, base, pos, mark, mx, my;

		for (k in il.COPagePres.iim_marker) {
			m = il.COPagePres.iim_marker[k];
			
			base = $("img#base_img_" + m.iim_id);
			pos = base.position();
			mark = $("a#" + m.m_id);
			mark.css('position', 'absolute');
			mx = parseInt(m.markx, 10);
			my = parseInt(m.marky, 10);
			mark.css('left', pos.left + mx + $("#fixed_content").scrollLeft());
			mark.css('top', pos.top + my + $("#fixed_content").scrollTop());
		}
	},
	
	/**
	 * Make marker draggable
	 */
	startDraggingMarker: function(tr_nr) {
		var k, mark;
		
		this.dragging = true;
		for (k in il.COPagePres.iim_marker) {
			if (il.COPagePres.iim_marker[k].tr_nr == tr_nr) {
				mark = il.COPagePres.iim_marker[k];
				$("a#" + il.COPagePres.iim_marker[k].m_id).css("display", "");
				il.COPagePres.fixMarkerPositions();
				$("a#" + il.COPagePres.iim_marker[k].m_id).draggable({
					drag: function(event, ui) {
						var base, bpos, marker, mpos, position;

						base = $("img#base_img_" + mark.iim_id);
						bpos = base.position();
						marker = $("a#" + mark.m_id);
						mpos = marker.position();
						position = (Math.round(mpos.left) - Math.round(bpos.left)) + "," +
							(Math.round(mpos.top) - Math.round(bpos.top));
						$("input#markpos_" + mark.tr_nr).attr("value", position);
					}
				});

				il.COPagePres.initDragToolbar();
			}
			else
			{
				$("a#" + il.COPagePres.iim_marker[k].m_id).css("display", "none");
			}
		}
	},
	
	stopDraggingMarker: function() {
		this.dragging = false;
	},
	
	/**
	 * Make overlay draggable
	 */
	startDraggingOverlay: function(tr_nr) {
		var k, trigger, dtr, ov, base, bpos, ovx, ovy;
		
		this.dragging = true;

		for (k in il.COPagePres.iim_trigger) {
			trigger = il.COPagePres.iim_trigger[k];

			if (trigger.nr == tr_nr) {
				dtr = trigger;
				ov = $("img#iim_ov_" + dtr.tr_id);
				
				// remove map for dragging
				ov.attr('usemap','');

				il.COPagePres.initDragToolbar();
				
				base = $("img#base_img_" + dtr.iim_id);
				bpos = base.position();
				ovx = parseInt(dtr.ovx, 10);
				ovy = parseInt(dtr.ovy, 10);
				ov.css('left', bpos.left + ovx + $("#fixed_content").scrollLeft());
				ov.css('top', bpos.top + ovy + $("#fixed_content").scrollTop());
				ov.css('display', '');
				ov.css("position", "absolute");

				dtr = trigger;
				ov.draggable({
					stop: function(event, ui) {
						var ovpos, position;

						ovpos = ov.position();
						position = (Math.round(ov.offset().left) - Math.round(base.offset().left)) + "," +
							(Math.round(ov.offset().top) - Math.round(base.offset().top));

						$("input#ovpos_" + dtr.nr).attr("value", position);
					}
				});
			}
		}
	},
	
	/**
	 * Make popup draggable
	 */
	startDraggingPopup: function(tr_nr) {
		var i, k, dtr, cpop, pdummy, base, bpos, popx, popy;

		this.dragging = true;

		// get correct trigger
		for (k in il.COPagePres.iim_trigger) {
			if (il.COPagePres.iim_trigger[k].nr == tr_nr) {
				dtr = il.COPagePres.iim_trigger[k];
				
				// get correct popup
				for (i in il.COPagePres.iim_popup) {
					if (il.COPagePres.iim_popup[i].nr ==
						il.COPagePres.iim_trigger[k].popup_nr) {

						cpop = il.COPagePres.iim_popup[i];
						pdummy = document.getElementById("popupdummy");
						if (pdummy == null) {
							$('div#il_center_col').append('<div id="popupdummy" class="ilc_iim_ContentPopup"></div>');
							pdummy = $("div#popupdummy");
						} else {
							pdummy = $("div#popupdummy");
						}

						il.COPagePres.initDragToolbar();
						
						base = $("img#base_img_" + cpop.iim_id);
						bpos = base.position();
//console.log(dtr);
						popx = parseInt(dtr.popx, 10);
						popy = parseInt(dtr.popy, 10);
						pdummy.css("position", "absolute");
						pdummy.css('left', bpos.left + popx + $("#fixed_content").scrollLeft());
						pdummy.css('top', bpos.top + popy + $("#fixed_content").scrollTop());
						pdummy.css('width', dtr.popwidth);
						pdummy.css('height', dtr.popheight);
						pdummy.css('display', '');
						
						pdummy.draggable({
							stop: function(event, ui) {
								var pdpos, position;

								pdpos = pdummy.position();
								position = (Math.round(pdummy.offset().left) - Math.round(base.offset().left)) + "," +
									(Math.round(pdummy.offset().top) - Math.round(base.offset().top));
								$("input#poppos_" + dtr.nr).attr("value", position);
							}
						});
					}
				}
			}
		}
	},

	/**
	 * Init drag toolbar
	 */
	initDragToolbar: function() {
		// show the toolbar
		$("#drag_toolbar").removeClass("ilNoDisplay");
		this.fixMarkerPositions();
		$("#save_pos_button").click(function () {
			$("input#update_tr_button").trigger("click");
			});
	},

	
	//
	// Question Overviews
	//

	qover: {},
	ganswer_data: {},
	
	addQuestionOverview: function(conf) {
		this.qover[conf.id] = conf;
	},
	
	updateQuestionOverviews: function() {
		var correct = {},
			incorrect = {},
			correct_cnt = 0,
			incorrect_cnt = 0,
			answered_correctly, index, k, i, ov_el,ul, j, qtext;
		
		if (typeof questions === 'undefined') {
			return;
		}
		
		for (k in questions) {
			answered_correctly = true;
			index=parseInt(k, 10);
			if (!isNaN(index)) {
				if (!answers[index]) {
					answered_correctly = false;
				} else {
					if (answers[index].passed!=true) {
						answered_correctly = false;
					}
				}
				if (!answered_correctly) {
					incorrect[k] = k;
					incorrect_cnt++;
				} else {
					correct[k] = k;
					correct_cnt++;
				}
			}
		}

		// iterate all question overview elements
		for (i in this.qover) {
			ov_el = $('div#' + this.qover[i].div_id);
			
			// remove all children
			ov_el.empty();
			
			// show success message, if all questions have been answered
			if (incorrect_cnt == 0) {
				ov_el.attr("class", 'ilc_qover_Correct');
				ov_el.append(
					ilias.questions.txt.ov_all_correct);
			} else {
				ov_el.attr("class", 'ilc_qover_Incorrect');
				// show message including of number of not
				// correctly answered questions
				if (this.qover[i].short_message == "y") {
					ov_el.append('<div class="ilc_qover_StatusMessage">' +
						ilias.questions.txt.ov_some_correct.split("[x]").join(String(correct_cnt))
							.split("[y]").join(String(incorrect_cnt + correct_cnt)) +
							"</div>"
						);
				}
				
				if (this.qover[i].list_wrong_questions == "y") {
					ov_el.append(
						'<div class="ilc_qover_WrongAnswersMessage">' +
						ilias.questions.txt.ov_wrong_answered + ":" + '</div>'
						);
					
					// list all incorrect answered questions
					ov_el.append('<ul class="ilc_list_u_BulletedList"></ul>');
					ul = $('div#' + this.qover[i].div_id + " > ul");
					for (j in incorrect) {
						qtext = questions[j].question;

						if (questions[j].type == "assClozeTest") {
							qtext = questions[j].title;
						}

						ul.append(
							'<li class="ilc_list_item_StandardListItem">' +
							'<a href="#" onclick="return il.COPagePres.jumpToQuestion(\'' + j + '\');" class="ilc_qoverl_WrongAnswerLink">' + qtext + '</a>'
							+ '</li>');
					}
				}
			}
		}
	},

	// jump to a question
	jumpToQuestion: function(qid) {
		if (typeof pager !== "undefined") {
			pager.jumpToElement("container" + qid);
		}
		return false;
	},

	setGivenAnswerData: function (data) {
		ilCOPagePres.ganswer_data = data;
	},

	//
	// Map area functions
	//
	
	// init map areas
	initMapAreas: function() {
		
		$('img[usemap^="#map_il_"][class!="ilIim"]').maphilight({"neverOn":true});
	},
	
	////
	//// Handle advanced content
	////
	showadvcont: true,
	initAdvancedContent: function() {
		var c = $("div.ilc_section_AdvancedKnowledge"),
			b = $("#ilPageShowAdvContent"), cookiePos;
		if (c.length > 0 && b.length > 0) {
			cookiePos = document.cookie.indexOf("pg_hideadv=");
			if (cookiePos > -1 && document.cookie.charAt(cookiePos + 11) == 1) {
				this.showadvcont = false;
			}

			$("#ilPageShowAdvContent").css("display", "block");
			if (il.COPagePres.showadvcont) {
				$("div.ilc_section_AdvancedKnowledge").css("display", "");
				$("#ilPageShowAdvContent > span:nth-child(1)").css("display", "none");
			} else {
				$("div.ilc_section_AdvancedKnowledge").css("display", "none");
				$("#ilPageShowAdvContent > span:nth-child(2)").css("display", "none");
			}
			$("#ilPageShowAdvContent").click(function () {
				if (il.COPagePres.showadvcont) {
					$("div.ilc_section_AdvancedKnowledge").css("display", "none");
					$("#ilPageShowAdvContent > span:nth-child(1)").css("display", "");
					$("#ilPageShowAdvContent > span:nth-child(2)").css("display", "none");
					il.COPagePres.showadvcont = false;
					document.cookie = "pg_hideadv=1";
				} else {
					$("div.ilc_section_AdvancedKnowledge").css("display", "");
					$("#ilPageShowAdvContent > span:nth-child(1)").css("display", "none");
					$("#ilPageShowAdvContent > span:nth-child(2)").css("display", "");
					il.COPagePres.showadvcont = true;
					document.cookie = "pg_hideadv=0";
				}
				return false;
			});
		}
	},
	
	////
	//// Audio/Video
	////
	
	initAudioVideo: function () {

		if ($('video.ilPageVideo,audio.ilPageAudio').mediaelementplayer) {
			$('video.ilPageVideo,audio.ilPageAudio').each(function(i, el) {
				var def, cfg;

				def = $(el).find("track[default='default']").first().attr("srclang");
				cfg = {};
				if (def != ""){
					cfg.startLanguage = def;
				}
				$(el).mediaelementplayer(cfg);
			});
		}
	}

};
il.Util.addOnLoad(function() {il.COPagePres.init();});
