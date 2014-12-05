(function ($) {

	$.fn.ilChatViewer = function(method) {

		var internals = {
			lastPrintedDate: null,
			translate: function(key) {
				var data = this.data('chatviewer');
				
				if (data.properties.lang[key]) {
					var lng = data.properties.lang[key];
					if (typeof arguments[1] != 'undefined') {
						for (var i in arguments[1]) {
							lng = lng.replace(new RegExp('#' + i + '#', 'g'), arguments[1][i]);
						}
					}
					return lng;
				}
				return '#' + key + '#';
			},
			getNewMessages:   function () {
				var $this = this, data = $this.data('chatviewer');

				var promise = $.ajax({
					type:    'GET',
					dataType:'json',
					url:     internals.getPollingUrl.apply($this),
					beforeSend: function() {
						$(data.properties.message_container_selector).html($('<img src="' + data.properties.loaderImage + '" />'));
					}
				});
				promise.done(function (response) {
					try {
						if (!response.messages)
							return;
						if (response.ok == true) {
							$this.ilChatViewer('emptyMessageBody');

							$(response.messages).each(function () {
								if (this.type == 'connected' || this.type == 'disconnected') {
									if (this.users) {
										var message = this;
										$(message.users).each(function () {
											internals.appendNewMessages.call($this, {
												type: message.type,
												message: message,
												login: this.login
											});
										});
									}
								}
								else {
									internals.appendNewMessages.call($this, {
										type: this.type,
										message: this
									});
								}
							});

						}
					}
					catch (e) {
						console.log(e);
					}
				});
			},
			getPollingUrl:    function () {
				var data = this.data('chatviewer');
				var url = data.properties.base_url.replace(
					/col_side=(left|right)/,
					'col_side=' + (
						$(data.properties.message_container_selector).closest('#il_left_col').size() ? 'left' : 'right'
					)
				);
				return url.replace(/#__ref_id/, $(data.properties.room_selector).val()) + '&chatBlockCmd=getMessages';
			},
			getChatroomListUrl:    function () {
				var data = this.data('chatviewer');
				var url = data.properties.base_url.replace(
					/col_side=(left|right)/,
					'col_side=' + (
						$(data.properties.message_container_selector).closest('#il_left_col').size() ? 'left' : 'right'
					)
				);
				return url.replace(/#__ref_id/, '') + '&chatBlockCmd=getChatroomSelectionList';
			},
			doAutoscroll:     function () {
				var data = this.data('chatviewer');
				
				return $(data.properties.autoscroll_selector).attr("checked");
			},
			formatISOTime:    function (date) {
				var $this = $(this), format = internals.translate.call($this, "timeformat");

				format = format.replace(/H/, internals.formatToTwoDigits(date.getHours()));
				format = format.replace(/i/, internals.formatToTwoDigits(date.getMinutes()));
				format = format.replace(/s/, internals.formatToTwoDigits(date.getSeconds()));

				return format;
			},
			formatISODate:    function(date) {
				var $this = $(this), format = internals.translate.call($this, "dateformat");

				format = format.replace(/Y/, date.getFullYear());
				format = format.replace(/m/, internals.formatToTwoDigits(date.getMonth() + 1));
				format = format.replace(/d/, internals.formatToTwoDigits(date.getDate()));
	
				return format;
			},
			formatToTwoDigits:function (nr) {
				nr = '' + nr;
				while (nr.length < 2) {
					nr = '0' + nr;
				}
				return nr;
			},
			replaceSmileys:   function (message) {
				var data = this.data('chatviewer'),
					replacedMessage = message;

				for (var i in data.properties.smilies) {
					while (replacedMessage.indexOf(i) != -1) {
						replacedMessage = replacedMessage.replace(i, '<img src="' + data.properties.smilies[i] + '" />');
					}
				}
				
				return replacedMessage;
			},
			appendNewMessages:function (message) {
				var $this = $(this),
					data = $this.data('chatviewer');

				var $container = $(data.properties.message_container_selector);

				var line = $('<div class="messageLine chat"></div>').addClass('public');

				if (message.message && message.message.message) {
					message = message.message;
				}

				var messageDate;
				if(typeof message.timestamp == "undefined" && typeof message.message.timestamp != "undefined"){
					messageDate =  new Date(message.message.timestamp);
				} else if(typeof message.timestamp != "undefined") {
					messageDate =  new Date(message.timestamp);
				}

				if (typeof messageDate != "undefined" &&
					(typeof internals.lastPrintedDate == "undefined" ||
					internals.lastPrintedDate == null ||
					internals.lastPrintedDate.getDate() != messageDate.getDate() ||
					internals.lastPrintedDate.getMonth() != messageDate.getMonth() ||
					internals.lastPrintedDate.getFullYear() != messageDate.getFullYear())) {
					$container.append($('<div class="messageLine chat dateline"><span class="chat content date">' + internals.formatISODate.call($this, messageDate) + '</span><span class="chat content username"></span><span class="chat content message"></span></div>'));
				}
				internals.lastPrintedDate = messageDate;
				
				switch (message.type) {
					case 'message':
						var content;
						try {
							content = $.parseJSON(message.message);
						}
						catch (e) {
							return;
						}

						line.append($('<span class="chat content date"></span>').append('' + internals.formatISOTime.call($this, messageDate) + ', '))
							.append($('<span class="chat content username"></span>').append(message.user.username));

						if (message.recipients) {
							var parts = message.recipients.split(',');
							for (var i in parts) {
								if (parts[i] != message.user.id) {
									line.append($('<span class="chat recipient">@</span>').append('unkown'));
								}
							}
						}

						var messageSpan = $('<span class="chat content message"></span>');
						messageSpan.text(messageSpan.text(content.content).text())
							.html(internals.replaceSmileys.call($this, messageSpan.text()));
						line.append($('<span class="chat content messageseparator">:</span>'))
							.append(messageSpan);

						for (var i in content.format) {
							if (i != 'color')
								messageSpan.addClass(i + '_' + content.format[i]);
						}

						messageSpan.css('color', content.format.color);

						break;

					case 'connected':
						return;
						
						if (message.login || (message.message.users[0] && this.message.users[0].login)) {
							line.append($('<span class="chat"></span>').append(internals.translate.call($this, 'connect', {username: message.login})));
							line.addClass('notice');
						}
						break;

					case 'disconnected':
						if (message.login || (message.message.users[0] && message.message.users[0].login)) {
							line.append($('<span class="chat"></span>').append(internals.translate.call($this, 'disconnected', {username: message.login})));
							line.addClass('notice');
						}
						break;

					default:
						return;
						break;
				}

				$container.append(line);

				if (internals.doAutoscroll.call($this)) {
					$container.scrollTop(100000);
				}
			}
		};

		var methods = {
			init: function (params) {
				return this.each(function () {
					var $this = $(this);

					// prevent double initialization
					if ($this.data('chatviewer')) {
						return;
					}

					var data = {
						properties:     $.extend(true, {}, {
							base_url:                  '',
							loaderImage:               '',
							message_container_selector:'',
							message_header_selector:   '',
							room_selector:             '',
							room_selector_container:   '',
							autoscroll_selector:       '',
							polling_interval:          20,
							smilies:                   {},
							lang:                      {}
						}, params),
						interval_handle:undefined
					};

					$this.data('chatviewer', data);

					var promise = $.ajax({
						type:     'GET',
						dataType: 'json',
						url:      internals.getChatroomListUrl.apply($this),
						beforeSend: function() {
							$(data.properties.room_selector_container).html($('<img src="' + data.properties.loaderImage + '" />'));
						}
					});
					promise.done(function(response) {
						try {
							if (!response.ok) {
								return;
							}
							$(data.properties.room_selector_container).html(response.html);

							$(data.properties.room_selector).on('change', function () {
								$this.ilChatViewer('onRoomChange');
							});

							if ($(data.properties.room_selector).val()) {
								$this.ilChatViewer('onRoomChange');
							}
						} catch(e) {
							console.log(e);
						}
					});
				});
			},
			onRoomChange: function () {
				var $this = $(this),
					data = $this.data('chatviewer');

				if (data.interval_handle) {
					window.clearInterval(data.interval_handle);
				}

				$this.ilChatViewer('emptyMessageBody');
				if ($(data.properties.room_selector).val() == 0) {
					$(data.properties.message_container_selector).hide();
					$(data.properties.message_header_selector).hide();
				} else {
					$(data.properties.message_container_selector).show();
					$(data.properties.message_header_selector).show();
				}

				internals.getNewMessages.call($this);

				data.interval_handle = window.setInterval(function () {
					internals.getNewMessages.call($this);
				}, data.properties.polling_interval * 1000);
			},
			emptyMessageBody: function () {
				var $this = $(this),
					data = $this.data('chatviewer');
				$(data.properties.message_container_selector).empty();
			}
		};

		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('Method ' + method + ' does not exist on jQuery.ilChatViewer');
		}

	};

})(jQuery);