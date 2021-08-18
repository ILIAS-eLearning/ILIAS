var OSDNotifier, OSDNotifications = function (settings) {
	$.extend(
		{
			initialNotifications: [],
			pollingIntervall:     0,
			closeHtml:            ''
		},
		settings
	);

	return (function () {
		return new function () {
			var me = this,
				lastRequest = 0,
				items = {};

			$("body").append('<div class="osdNotificationContainer"></div>');

			$(settings.initialNotifications).each(function () {
				items['osdNotification_' + this.notification_osd_id] = this;
			});

			function closeNotification(notificationElement) {
				notificationElement.remove();
			}

			this.removeNotification = function (id, callback) {
				$.get(
					"ilias.php",
					{
						baseClass:       'ilObjChatroomGUI',
						cmd:             'removeOSDNotifications',
						cmdMode:         'asynch',
						notification_id: id
					},
					function (data) {
						closeNotification($('#osdNotification_' + id));
						if (items['osdNotification_' + id])
							delete items['osdNotification_' + id];

						if (typeof callback === 'function') {
							callback();
						}
					}
				);
			};

			function getParam(params, ns, defaultValue) {
				if (typeof params === 'undefined')
					return defaultValue;

				var parts = ns.split('.', 2);
				if (parts.length > 1) {
					return (!params[parts[0]] || typeof params[parts[0]][parts[1]] === 'undefined') ? defaultValue : params[parts[0]][parts[1]];
				}
				else {
					return (!params[ns]) ? defaultValue : params[ns];
				}
			}

			function renderItems(data, init) {

				var currentTime = parseInt(new Date().getTime() / 1000),
					newItems = false;

				$(data.notifications).each(function () {
					if (this.type === 'osd_maint') {
						if (this.data.title === 'deleted') {
							closeNotification($('#osdNotification_' + this.data.shortDescription));
						}
					} else {
						var id = this.notification_osd_id;
						if ($('#osdNotification_' + id).length == 0 && (this.valid_until > currentTime || this.valid_until == 0)) {
							newItems = true;
							var newElement = $(
								'<div class="osdNotification" id="osdNotification_' + this.notification_osd_id + '">'
									+ ((getParam(this.data.handlerParams, 'osd.closable', true)) ? ('<div class="btn-link" style="float: right" onclick="OSDNotifier.removeNotification(' + this.notification_osd_id + ')">' + settings.closeHtml + '</div>') : '')
									+ '<div class="osdNotificationTitle">'
									+ (this.data.iconPath ? '<img class="osdNotificationIcon" src="' + this.data.iconPath + '" alt="" />' : '')
									+ (this.data.link ? ('<a class="target_link" href="' + this.data.link + '" target="' + this.data.linktarget + '">' + this.data.title + '</a>') : this.data.title)
									+ '</div>'
									+ '<div class="osdNotificationShortDescription">' + this.data.shortDescription + '</div>'
									+ '</div>'
							);
							
							$('.osdNotificationContainer').append(newElement);
							if (getParam(this.data.handlerParams, 'osd.closable', true)) {
								let href = newElement.find('.target_link').attr('href');
								newElement.find('.target_link').on("click", function () {
									me.removeNotification(id, function () {
										window.location.href = href;
									});
								});
							}

							newElement.find('.osdNotificationShortDescription a').on("click", function () {
								me.removeNotification(id);
							});

							if (this.visible_for != 0) {
								window.setTimeout(function() {
									me.removeNotification(id);
								}, this.visible_for * 1000);
							}
						}
						items['osdNotification_' + this.notification_osd_id] = this;
					}

					if (!init && settings.playSound && newItems) {
						var id = 'notification_' + Math.random().toString(36).substr(2, 5),
							$notielm;

						$notielm = $('<audio id="' + id + '"></audio>');
						$notielm.append($('<source src="Modules/Chatroom/sounds/receive.mp3" type="audio/mp3" />'));
						$notielm.append($('<source src="Modules/Chatroom/sounds/receive.ogg" type="audio/ogg" />'));
						$notielm.css({
							width: 0,
							height: 0
						});

						$("body").append($notielm);

						$
							.when($("body").append($notielm))
							.then(function() {
								var p = $("#" + id).get(0).play();

								if (p !== undefined) {
									p.then(function() {
										console.log("Played sound successfully!");
									}).catch(function(e) {
										console.log("Could not play sound, autoplay policy changes: https://developers.google.com/web/updates/2017/09/autoplay-policy-changes");
									});
								}
							});
					}
				});

				$.each(items, function () {
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
						cmdMode:  'asynch',
						/*
						 * minus 10 seconds for getting really all messages, even if they
						 * arrived while processing
						 */
						max_age:  Math.abs(lastRequest - 10 - (parseInt(new Date().getTime() / 1000)))
					},
					function (data) {
						if (typeof data !== "object") {
							return;
						}

						lastRequest = parseInt(new Date().getTime() / 1000);

						renderItems(data);

						if (settings.pollingIntervall * 1000) {
							window.setTimeout(me.poll, settings.pollingIntervall * 1000);
						}

					},
					'json'
				);
			};

			renderItems({notifications:settings.initialNotifications}, true);

			if (settings.pollingIntervall * 1000) {
				window.setTimeout(me.poll, settings.pollingIntervall * 1000);
			}
		};
	})();
};