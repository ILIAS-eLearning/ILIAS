il.MediaObjects = {

	current_player: null,
	current_player_id: null,
	current_wrapper: '',

	init: function() {
		$(".ilPlayerPreviewOverlayOuter").click(function (e) {
				il.MediaObjects.processMediaPreviewClick(this, e);
		});
	},

	// click on a media preview picture
	processMediaPreviewClick: function (t,e) {
		var video_el, player, video_el_wrap, audio_el;

		// stop current player, if already playing
		il.MediaObjects.stopCurrentPlayer();
		
		// video ?
		video_el = $(t).find('video');
		if (video_el.length > 0) {
			$(t).find('.ilPlayerPreviewOverlay').addClass('ilNoDisplay');
			video_el_wrap = $('#' + video_el.attr('id') + "_vtwrap");
			
			il.Lightbox.activateView('media_lightbox');
			//il.Lightbox.onDeactivation('media_lightbox', il.MediaObjects.onLightboxDeactivation);
			il.Lightbox.loadWrapperToLightbox(video_el.attr('id') + "_wrapper", "media_lightbox");
	
			video_el.removeClass('ilNoDisplay');
			video_el_wrap.removeClass('ilNoDisplay');
			video_el.attr('autoplay', 'true');
			player = new MediaElementPlayer('#' + video_el.attr('id'), {});
			// this fails in safari if a flv file has been called before
			//player.play();
			il.MediaObjects.current_player_id = video_el.attr('id');
			il.MediaObjects.current_player = player;
		} else {
			// audio ?
			audio_el = $(t).parent().find('audio');
			if (audio_el.length > 0) {
				player = $('#' + audio_el.attr('id'))[0].player.media;
				player.play();
				il.MediaObjects.current_player_id = audio_el.attr('id');
				il.MediaObjects.current_player = player;
			} else {
				
				// image?
				if ($(t).hasClass('ilPlayerPreviewImage')) {
					$(t).find('.ilPlayerPreviewOverlay').addClass('ilNoDisplay');
					var img_el = $(t).parent().find('div.ilPlayerImage');
//					video_el_wrap = $('#' + video_el.attr('id') + "_vtwrap");
					
					il.Lightbox.activateView('media_lightbox');
					//il.Lightbox.onDeactivation('media_lightbox', il.MediaObjects.onLightboxDeactivation);
//console.log(img_el);
					img_el.removeClass('ilNoDisplay');
					il.Lightbox.loadWrapperToLightbox($(t).parent().attr('id'), "media_lightbox");
					//video_el_wrap.removeClass('ilNoDisplay');
					il.MediaObjects.current_player_id = img_el.attr('id');
				}
			}
		}
	},

	onLightboxDeactivation: function(id) {
		il.MediaObjects.stopCurrentPlayer();
	},

	processCloseIcon: function() {
		il.Lightbox.deactivateView('media_lightbox');
		il.MediaObjects.stopCurrentPlayer();
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
				il.MediaObjects.current_player.pause();
			}
		}
	}
}
il.Util.addOnLoad(il.MediaObjects.init);
