il.MediaObjects = {

	current_player: null,
	current_player_id: null,
	current_wrapper: '',
	player_config: {}, 
	lb_opened: false,

	init: function() {
		$(".ilPlayerPreviewOverlayOuter").click(function (e) {
				il.MediaObjects.processMediaPreviewClick(this, e);
		});
		$(".ilPlayerPreviewOverlayOuter").keypress(function (e) {
				il.MediaObjects.processMediaPreviewClick(this, e);
		});
		$(".ilPlayerPreviewDescriptionDownload a").click(function (e) {
			il.MediaObjects.processDownloadLink(this, e);
		});

		window.onhashchange = function() {
			il.MediaObjects.onHashChange();
		}
	},
	
	setPlayerConfig: function (id, config) {
		il.MediaObjects.player_config[id] = config;
	},

	// click on a media preview picture
	processMediaPreviewClick: function (t,e) {
		var video_el, player, video_el_wrap, audio_el;

		// stop current player, if already playing
		il.MediaObjects.stopCurrentPlayer();
		
		// video ?
		video_el = $(t).find('video');
		if (video_el.length > 0) {
			const video_el_id = video_el.parent().attr('id');
			$(t).find('.ilPlayerPreviewOverlay').addClass('ilNoDisplay');
			video_el_wrap = $('#' + video_el_id + "_vtwrap");
			
			il.Lightbox.activateView('media_lightbox');
			location.hash = "detail";
			il.MediaObjects.lb_opened = true;
			
			//il.Lightbox.onDeactivation('media_lightbox', il.MediaObjects.onLightboxDeactivation);
			il.Lightbox.loadWrapperToLightbox(video_el_id + "_wrapper", "media_lightbox");
	
			//video_el.removeClass('ilNoDisplay');
			video_el_wrap.removeClass('ilNoDisplay');
			video_el_wrap.find(".ilNoDisplay").removeClass('ilNoDisplay');
			video_el.attr('autoplay', 'true');
			const elid = video_el.attr('id');
			player = new MediaElementPlayer(elid, {});
			const wrap = document.getElementById(elid);
			if (!il.MediaObjects.player_config[video_el_id]['listener_added']) {
				wrap.addEventListener('play', function (e) {
					il.MediaObjects.playerStarted();
				}, false);
			}
			il.MediaObjects.player_config[video_el_id]['listener_added'] = true;
			// this fails in safari if a flv file has been called before
			il.MediaObjects.current_player_id = video_el_id;
			player.play();
			il.MediaObjects.current_player = player;
		} else {
			// audio ?
			audio_el = $(t).parent().find('audio');
			if (audio_el.length > 0) {
				player = $('#' + audio_el.attr('id'))[0].player.media;
				const audio_el_id = audio_el.parent().attr('id');
				il.MediaObjects.current_player_id = audio_el_id;
				player.play();
				il.MediaObjects.current_player = player;
			} else {

				// image?
				if ($(t).hasClass('ilPlayerPreviewImage')) {
					$(t).find('.ilPlayerPreviewOverlay').addClass('ilNoDisplay');
					var img_el = $(t).parent().find('div.ilPlayerImage');
//					video_el_wrap = $('#' + video_el.attr('id') + "_vtwrap");
					
					il.Lightbox.activateView('media_lightbox');
					location.hash = "detail";
					il.MediaObjects.lb_opened = true;
					
					//il.Lightbox.onDeactivation('media_lightbox', il.MediaObjects.onLightboxDeactivation);
//console.log(img_el);
					img_el.removeClass('ilNoDisplay');
					il.Lightbox.loadWrapperToLightbox($(t).parent().attr('id'), "media_lightbox");
					//video_el_wrap.removeClass('ilNoDisplay');
					il.MediaObjects.current_player_id = img_el.attr('id');
					
					il.MediaObjects.playerStarted();
				} else {
					$(t).find('.ilPlayerPreviewOverlay').addClass('ilNoDisplay');
					o_el = $(t).find('object, iframe');
					il.Lightbox.activateView('media_lightbox');
					location.hash = "detail";
					il.MediaObjects.lb_opened = true;
					o_el_wrap = $('#' + o_el.attr('id') + "_vtwrap");
					//il.Lightbox.onDeactivation('media_lightbox', il.MediaObjects.onLightboxDeactivation);
//console.log(img_el);
					o_el_wrap.removeClass('ilNoDisplay');
					o_el.removeClass('ilNoDisplay');
					il.Lightbox.loadWrapperToLightbox(o_el.attr('id') + "_wrapper", "media_lightbox");
					//video_el_wrap.removeClass('ilNoDisplay');
					il.MediaObjects.current_player_id = o_el.attr('id');

					//il.MediaObjects.playerStarted(il.MediaObjects.current_player_id);
				}
			}
		}
	},

	processDownloadLink: function (t,e) {
		e.stopPropagation();
	},

	onLightboxDeactivation: function(id) {
		il.MediaObjects.stopCurrentPlayer();
		il.MediaObjects.lb_opened = false;
		location.hash = "";
	},

	processCloseIcon: function() {
		il.Lightbox.deactivateView('media_lightbox');
		il.MediaObjects.stopCurrentPlayer();
		il.MediaObjects.lb_opened = false;
		location.hash = "";
	},
	
	onHashChange: function () {
		if (location.hash == "" && il.MediaObjects.lb_opened) {
			il.MediaObjects.processCloseIcon();
		}
	},

	stopCurrentPlayer: function () {
		var video_el, video_el_wrap;
		if (il.MediaObjects.current_player_id) {
			video_el = $('#' + il.MediaObjects.current_player_id);
			if (video_el.hasClass('ilPlayerImage')) {
				video_el.addClass('ilNoDisplay');
				video_el.parent().find('.ilPlayerPreviewOverlay').removeClass('ilNoDisplay');
			} else {
				video_el_wrap = $('#' + il.MediaObjects.current_player_id + "_vtwrap");
				video_el.attr('autoplay', 'false');
				video_el.addClass('ilNoDisplay');
				video_el_wrap.addClass('ilNoDisplay');
				$('#' + il.MediaObjects.current_player_id + "_wrapper").find('.ilPlayerPreviewOverlay').removeClass('ilNoDisplay');
				
				il.MediaObjects.current_player_id = null;
				
				// the next line currently fails on safari if a flv file has been called:
				// TypeError: 'undefined' is not a function (evaluating 'this.pluginApi.pauseMedia()'), see also:
				// http://stackoverflow.com/questions/10487575/show-hiding-video-container-produces-pluginapi-errors-mediaelement-js
				if (il.MediaObjects.current_player) {
					il.MediaObjects.current_player.pause();
				}
			}
		}
	},
	
	playerStarted: function () {
		const id = il.MediaObjects.current_player_id;
		console.log("PLAYER STARTED: " + il.MediaObjects.current_player_id);
		var url;
		if (typeof il.MediaObjects.player_config[id] != "undefined" &&
			typeof il.MediaObjects.player_config[id]['play_event_sent'] == "undefined") {
			url = il.MediaObjects.player_config[id].event_url;
			if (url != "") {
				url = url + "&event=play&player=" + id;
				il.MediaObjects.player_config[id]['play_event_sent'] = true;
				console.log("SENDING START REQUEST");
				il.Util.sendAjaxGetRequestToUrl(url, {}, {}, null);
			}
		}
	},

	autoInitPlayers: function () {
		$("video, audio").each(function () {
			var id = $(this).attr("id");
			if ($(this).attr("id") != "") {
				new MediaElementPlayer(id);
			}

		});
	}
}
il.Util.addOnLoad(il.MediaObjects.init);
