var OSDNotifications = function (settings) {
	$.extend(
		{
			initialNotifications: [],
			pollingIntervall:     0,
			closeHtml:            ''
		},
		settings
	);

	return function () {
		return new function () {
			var me = this;

			var lastRequest = 0;

			var items = {};

			$(settings.initialNotifications).each(function () {
				items['osdNotification_' + this.notification_osd_id] = this;
			});

			function closeNotification(notificationElement) {
				$(notificationElement).animate({
					height: 0,
					opacity:0
				}, 1000, "linear", function () {
					notificationElement.remove();
				});
			}

			this.removeNotification = function (id, callback) {
				$.get(
					"ilias.php",
					{
						baseClass:      'ilObjChatroomGUI',
						cmd:            'removeOSDNotifications',
						notification_id:id
					},
					function (data) {
						closeNotification($('#osdNotification_' + id));
						if (items['osdNotification_' + id])
							delete items['osdNotification_' + id];

						if (typeof callback == 'function') {
							callback();
						}
					}
				);
			}

			function getParam(params, ns, defaultValue) {
				if (typeof params == 'undefined')
					return defaultValue;

				var parts = ns.split('.', 2);
				if (parts.length > 1) {
					return (!params[parts[0]] || typeof params[parts[0]][parts[1]] == 'undefined') ? defaultValue : params[parts[0]][parts[1]];
				}
				else {
					return (!params[ns]) ? defaultValue : params[ns];
				}
			}

			function renderItems(data, init) {
				var currentTime = parseInt(new Date().getTime() / 1000);

				var newItems = false;

				$(data.notifications).each(function () {
					if (this.type == 'osd_maint') {
						if (this.data.title == 'deleted') {
							closeNotification($('#osdNotification_' + this.data.shortDescription));
						}
					}
					else {
						var id = this.notification_osd_id;
						if ($('#osdNotification_' + id).length == 0 && (this.valid_until > currentTime || this.valid_until == 0)) {
							newItems = true;

							var newElement = $(
								'<div class="osdNotification" id="osdNotification_' + this.notification_osd_id + '">'
									+ ((getParam(this.data.handlerParams, 'osd.closable', true)) ? ('<div class="btn-link" style="float: right" onclick="OSDNotifier.removeNotification(' + this.notification_osd_id + ')">' + settings.closeHtml + '</div>') : '')
									+ '<div class="osdNotificationTitle"><img class="osdNotificationIcon" src="' + this.data.iconPath + '" alt="" />'
									+ (this.data.link ? ('<a class="target_link" href="' + this.data.link + '" target="' + this.data.linktarget + '">' + this.data.title + '</a>') : this.data.title)
									+ '</div>'
									+ '<div class="osdNotificationShortDescription">' + this.data.shortDescription + '</div>'
									+ '</div>'
							);
							$('.osdNotificationContainer').append(newElement);

							if (getParam(this.data.handlerParams, 'osd.closable', true)) {
								var href = newElement.find('.target_link').attr('href');
								newElement.find('.target_link').click(function () {
									me.removeNotification(id, function () {
										window.location.href = href;
									});

								});
							}
						}
						items['osdNotification_' + this.notification_osd_id] = this;
					}

					if (!init && settings.playSound && newItems) {
						//console.log('Ring');
						var id = 'notification_' + Math.random().toString(36).substr(2, 5);
						if($.browser.msie)
						{
							var $notielm = $('<audio id="' + id + '" src="Modules/Chatroom/sounds/receive.mp3" type="audio/mp3"></audio>');
						}
						else
						{
							var $notielm = $('<audio id="' + id + '"></audio>');
							$notielm.append($('<source src="Modules/Chatroom/sounds/receive.mp3" type="audio/mp3" />'));
							$notielm.append($('<source src="Modules/Chatroom/sounds/receive.ogg" type="audio/ogg" />'));
						}
						$notielm.css({
							width: 0,
							height: 0
						});
						$("body").append($notielm);
						var player = new MediaElementPlayer('#' + id, {
							plugins: ['flash','silverlight'],
							features: [],
							audioWidth: 0,
							audioHeight: 0
						});
						player.setVolume(1);
						player.play();
					}
				});

				$.each(items, function () {
					//console.log(this);
					if (this.valid_until < data.server_time && this.valid_until != 0) {

						closeNotification($('#osdNotification_' + this.notification_osd_id));
						if (items['osdNotification_' + this.notification_osd_id])
							delete items['osdNotification_' + this.notification_osd_id];
					}
				});
			}

			this.poll = function () {
				$.get(
					"ilias.php",
					{
						baseClass:'ilObjChatroomGUI',
						cmd:      'getOSDNotifications',
						/*
						 * minus 10 seconds for getting really all messages, even if they
						 * arrived while processing
						 */
						max_age:  Math.abs(lastRequest - 10 - (parseInt(new Date().getTime() / 1000)))
					},
					function (data) {
						lastRequest = parseInt(new Date().getTime() / 1000);

						renderItems(data);

						if (settings.pollingIntervall * 1000) {
							window.setTimeout(me.poll, settings.pollingIntervall * 1000);
						}

					},
					'json'
				);
			}

			renderItems({notifications:settings.initialNotifications}, true);

			if (settings.pollingIntervall * 1000) {
				window.setTimeout(me.poll, settings.pollingIntervall * 1000);
			}
		}
	}();
};