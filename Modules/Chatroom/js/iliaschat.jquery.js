jQuery.fn.extend({
	insertAtCaret: function (value) {
		return this.each(function (i) {
			if (document.selection) {
				//For browsers like Internet Explorer
				this.focus();
				sel = document.selection.createRange();
				sel.text = value;
				this.focus();
			}
			else if (this.selectionStart || this.selectionStart == '0') {
				//For browsers like Firefox and Webkit based
				var startPos = this.selectionStart;
				var endPos = this.selectionEnd;
				var scrollTop = this.scrollTop;
				this.value = this.value.substring(0, startPos) + value + this.value.substring(endPos, this.value.length);
				this.focus();
				this.selectionStart = startPos + value.length;
				this.selectionEnd = startPos + value.length;
				this.scrollTop = scrollTop;
			} else {
				this.value += value;
				this.focus();
			}
		})
	}
});

function formatToTwoDigits(nr) {
	nr = "" + nr;
	while (nr.length < 2) {
		nr = "0" + nr;
	}
	return nr;
}

function formatISOTime(time) {
	var format = translate("timeformat");
	var date = new Date(time);

	format = format.replace(/H/, formatToTwoDigits(date.getHours()));
	format = format.replace(/i/, formatToTwoDigits(date.getMinutes()));
	format = format.replace(/s/, formatToTwoDigits(date.getSeconds()));

	return format;
}

function formatISODate(time) {
	var format = translate("dateformat");
	var date = new Date(time);

	format = format.replace(/Y/, date.getFullYear());
	format = format.replace(/m/, formatToTwoDigits(date.getMonth() + 1));
	format = format.replace(/d/, formatToTwoDigits(date.getDate()));

	return format;
}

function isIdInArray(id, objects) {
	for (var i in objects) {
		if (typeof objects[i] != 'undefined' && typeof objects[i].id != 'undefined' && objects[i].id == id) {
			return true;
		}
	}
	return false;
}

var usermanager = (function () {

	var usersByRoom = {};

	return {
		add:         function (userdata, roomid) {
			if (!usersByRoom['room_' + roomid]) {
				usersByRoom['room_' + roomid] = [];
			}
			for (var i in usersByRoom['room_' + roomid]) {
				var current = usersByRoom['room_' + roomid][i];
				if (current.id == userdata.id) {
					//tmp.push(current);
					return false;
				}
			}
			usersByRoom['room_' + roomid].push(userdata);
			return true;
		},
		remove:      function (userid, roomid) {
			if (!usersByRoom['room_' + roomid]) {
				return;
			}

			var tmp = [];
			for (var i in usersByRoom['room_' + roomid]) {
				var current = usersByRoom['room_' + roomid][i];
				if (current.id != userid) {
					tmp.push(current);
				}
			}
			usersByRoom['room_' + roomid] = tmp;
		},
		inroom:      function (userid, roomid) {
			return isIdInArray(userid, usersByRoom['room_' + roomid]);
		},
		usersinroom: function (roomid) {
			return usersByRoom['room_' + roomid] || [];
		},
		clear:       function (roomid) {
			usersByRoom['room_' + roomid] = [];
		}
	}

})();

var translate, subRoomId, replaceSmileys;

il.Util.addOnLoad(function () {
	$("#submit_message_text").focus();

	function closeMenus() {
		$('.menu_attached').removeClass('menu_attached');
		$('.menu').hide();
	}

	return function ($) {

		$.getAsObject = function (data) {
			if (typeof data == 'object') {
				return data;
			}
			try {
				return JSON.parse(data);
			}
			catch (e) {
				if (typeof console != 'undefined') {
					console.log(e);
					return {success: false};
				}
			}
		}

		$.fn.chat = function (lang, baseurl, session_id, instance, scope, posturl, initial) {
			var smileys = initial.smileys;

			replaceSmileys = function (message) {
				if (typeof smileys == "string") {
					return message;
				}

				var replacedMessage = message;

				for (var i in smileys) {
					while (replacedMessage.indexOf(i) != -1) {
						replacedMessage = replacedMessage.replace(i, '<img src="' + smileys[i] + '" />');
					}
				}

				return replacedMessage;
			}

			if (typeof smileys == "object") {
				if (smileys.length == 0) {
					return;
				}
				// Emoticons
				var $emoticons_flyout_trigger = $('<a></a>');
				var $emoticons_flyout = $('<div id="iosChatEmoticonsPanelFlyout"></div>');
				var $emoticons_panel = $('<div id="iosChatEmoticonsPanel"></div>')
					.append($emoticons_flyout_trigger)
					.append($emoticons_flyout);

				$("#submit_message_text").css("paddingLeft", "25px").after($emoticons_panel);

				if ($.browser.chrome || $.browser.safari) {
					$emoticons_panel.css("top", "3px");
				}

				var $emoticons_table = $("<table></table>");
				var $emoticons_row = null;
				var cnt = 0;
				var emoticonMap = new Object();
				for (var i in smileys) {
					if (emoticonMap[smileys[i]]) {
						var $emoticon = emoticonMap[smileys[i]];
					} else {
						if (cnt % 6 == 0) {
							$emoticons_row = $("<tr></tr>");
							$emoticons_table.append($emoticons_row);
						}

						var $emoticon = $('<img src="' + smileys[i] + '" alt="" title="" />');
						$emoticon.data("emoticon", i);
						$emoticons_row.append($('<td></td>').append($('<a></a>').append($emoticon)));

						emoticonMap[smileys[i]] = $emoticon;

						++cnt;
					}
					$emoticon.attr({
						alt:   [$emoticon.attr('alt').toString(), i].join(' '),
						title: [$emoticon.attr('title').toString(), i].join(' ')
					});
				}
				$emoticons_flyout.append($emoticons_table);

				$emoticons_flyout_trigger.click(function (e) {
					$emoticons_flyout.toggle();
				}).toggle(function () {
					$(this).addClass("active");
				}, function () {
					$(this).removeClass("active");
				});

				$emoticons_panel.bind('clickoutside', function (event) {
					if ($emoticons_flyout_trigger.hasClass("active")) {
						$emoticons_flyout_trigger.click();
					}
				});

				$("#iosChatEmoticonsPanelFlyout a").click(function () {
					$emoticons_flyout_trigger.click();
					$("#submit_message_text").insertAtCaret($(this).find('img').data("emoticon"));
				});
			}

			$('#show_options').click(function () {
				if ($(this).next().is(':visible')) {
					$(this).text(translate('show_settings'));
				}
				else {
					$(this).text(translate('hide_settings'));
				}
				$(this).siblings().toggle();
			});


			$('<div id="tttt" style="white-space:nowrap;"></div>')
				.append($('#chat_actions_wrapper')).insertBefore($('.il_HeaderInner').find('h1'));

			if (!initial.private_rooms_enabled) {
				$('#chat_head_line').hide()
			}
			// keep session open
			window.setInterval(function () {
				$.get(posturl.replace(/postMessage/, 'poll'));
			}, 120 * 1000);

			personalUserInfo = initial.userinfo;

			translate = function (key) {
				if (lang[key]) {
					var lng = lang[key];
					if (typeof arguments[1] != 'undefined') {
						for (var i in arguments[1]) {
							lng = lng.replace(new RegExp('#' + i + '#', 'g'), arguments[1][i]);
						}
					}
					return lng;
				}
				return '#' + key + '#';
			}

			var prevSize = {width: 0, height: 0};
			window.setInterval(function () {
				var currentSize = {width: $('body').width(), height: $('body').height()};
				if (currentSize.width != prevSize.width || currentSize.height != prevSize.height) {
					$('#chat_sidebar_wrapper').height($('#chat_sidebar').parent().height() - $('#chat_sidebar_tabs').height());
					prevSize = {width: $('body').width(), height: $('body').height()};
				}
			}, 500);

			$('#chat_users').ilChatList([
				{
					label:    translate('address'),
					callback: function () {
						setRecipientOptions(this.id, 1);
					}
				},
				{
					label:    translate('whisper'),
					callback: function () {
						setRecipientOptions(this.id, 0);
					}
				},
				{
					label:      translate('kick'),
					callback:   function () {
						if (subRoomId) {
							/* alert('kick from private rooms coming soon.') */
							if (confirm(translate('kick_question'))) {
								kickUserOutOfSubroom(this.id);
							}
						}
						else if (confirm(translate('kick_question'))) {
							kickUser(this.id);
						}
					},
					permission: ['moderator', 'owner']
				},
				{
					label:      translate('ban'),
					callback:   function () {
						if (subRoomId) {
							alert('banning from private rooms coming soon.')
						}
						else if (confirm(translate('ban_question'))) {
							banUser(this.id);
						}
					},
					permission: ['moderator']
				}
			]);
			$('#private_rooms').ilChatList([
				{
					label:    translate('enter'),
					callback: function () {
						var room = this;
						$.get(
							posturl.replace(/postMessage/, 'privateRoom-enter') + '&sub=' + room.id,
							function (response) {
								if (typeof response != 'object') {
									response = $.getAsObject(response);
								}

								if (!response.success) {
									alert(response.reason);
								}

								subRoomId = room.id;

								$('#chat_messages').ilChatMessageArea('show', room.id, posturl);

							},
							'json'
						)
					}
				},
				{
					label:    translate('leave'),
					callback: function () {
						var room = this;
						$.get(
							posturl.replace(/postMessage/, 'privateRoom-leave') + '&sub=' + room.id,
							function (response) {
								if (typeof response != 'object') {
									response = $.getAsObject(response);
								}

								if (!response.success) {
									alert(response.reason);
								}

								$('#chat_messages').ilChatMessageArea('show', 0);

							},
							'json'
						)
					}
				},
				{
					label:      translate('delete_private_room'),
					callback:   function () {
						var room = this;
						$.get(
							posturl.replace(/postMessage/, 'privateRoom-delete') + '&sub=' + room.id,
							function (response) {
								if (typeof response != 'object') {
									response = $.getAsObject(response);
								}

								if (!response.success) {
									alert(response.reason);
								}
							},
							'json'
						)
					},
					permission: ['moderator', 'owner']
				}

			]);

			$('#chat_messages').ilChatMessageArea();

			$('#chat_messages').ilChatMessageArea('addScope', 0, {
				title: translate('main'),
				id:    0,
				owner: 0
			});

			$('#chat_messages').ilChatMessageArea('show', 0);


			var polling_url = baseurl + '/frontend/Poll/' + instance + '/' + scope + '?id=' + session_id;

			var messageOptions = {
				'recipient':      null,
				'recipient_name': null,
				'public':         1
			};

			$('#enter_main').click(function (e) {
				e.preventDefault();
				e.stopPropagation();
				subRoomId = 0;
				$('#chat_messages').ilChatMessageArea('show', 0);
				$('#chat_users').find('.online_user').not('.hidden_entry').show();
			});

			$('#submit_message').click(function () {
				submitMessage();
			});

			$('#submit_message_text').keydown(function (e) {
				if (e.keyCode == 13) {
					submitMessage();
				}
			});
			$('#tab_users').click(function (e) {
				e.stopPropagation();
				e.preventDefault();
				closeMenus();
				$([$('#tab_users'), $('#tab_users').parent()]).each(function () {
					this.removeClass('tabinactive').addClass('tabactive');
				});
				$([$('#tab_rooms'), $('#tab_rooms').parent()]).each(function () {
					this.removeClass('tabactive').addClass('tabinactive');
				});

				$('#chat_users').css('display', 'block');
				$('#private_rooms_wrapper').css('display', 'none');
			});

			$('#tab_users').click();

			$(initial.users).each(function () {
				var tmp = {
					id:    this.id,
					label: this.login,
					type:  'user',
					hide: this.id == personalUserInfo.userid
				};
				$('#chat_users').ilChatList('add', tmp, {hide: true});
				usermanager.add(tmp, 0);
			});

			$(initial.private_rooms).each(function () {
				$('#private_rooms').ilChatList('add', {
					id:    this.proom_id,
					label: this.title,
					type:  'room',
					owner: this.owner
				});
				$('#chat_messages').ilChatMessageArea('addScope', this.proom_id, this);
			});

			if (initial.enter_room) {
				$('#chat_messages').ilChatMessageArea('show', initial.enter_room, posturl);
				$(initial.messages).each(function () {
					var data = $('#private_rooms').ilChatList('getDataById', this.sub);

					if (this.sub == initial.enter_room && this.entersub == 1 && data) {
						$('#chat_messages').ilChatMessageArea('addMessage', initial.enter_room, {
							type:    this.type,
							message: translate('private_room_entered', {title: data.label})
						});
					}
				});
			}

			$(initial.messages).each(function () {
				//if (!this.sub) {
				if (this.type == 'connected' || this.type == 'disconnected') {
					if (this.users) {
						var message = this;
						$(message.users).each(function () {
							$('#chat_messages').ilChatMessageArea('addMessage', this.sub || 0, {
								type:    message.type,
								//message: this.message
								message: message,
								login:   this.login
							});
						});
					}
				}
				else {
					$('#chat_messages').ilChatMessageArea('addMessage', this.sub || 0, {
						type:    this.type,
						//message: this.message
						message: this
					});
				}
				//}
			});


			smileys = initial.smileys;

			function setRecipientOptions(recipient, isPublic) {
				messageOptions['recipient'] = recipient;
				messageOptions['public'] = isPublic;

				$('#message_recipient_info').children().remove();
				if (recipient) {
					messageOptions['recipient_name'] = $('#chat_users').ilChatList('getDataById', recipient).label;
					$('#message_recipient_info_all').hide();
					$('#message_recipient_info').html(
						$('<span>' + translate(isPublic ? 'speak_to' : 'whisper_to', {
							user:   $('#chat_users').ilChatList('getDataById', recipient).label,
							myname: personalUserInfo.name
						}) + '</span>')
							.append(
							$('<span>&nbsp;<a href="javascript:void(0)">(' + translate('end_whisper') + ')</a></span>').click(
								function () {
									setRecipientOptions(false, 1);
								}
							)
						)
					).show();
				}
				else {
					messageOptions['recipient_name'] = null;
					$('#message_recipient_info_all').show();
					$('#message_recipient_info').hide();
				}
			}

			function buildMoreOptions() {
				var res = [];
				for (var i in messageOptions) {
					if (messageOptions[i] == null || messageOptions[i] == false)
						continue;
					res.push(i + '=' + encodeURIComponent(messageOptions[i]));
				}
				if (subRoomId)
					res.push('sub=' + subRoomId);
				return res.join('&');
			}

			//var userdata = $('#chat_users').ilChatList('getDataById', personalUserInfo.userid);
			//usermanager.add(userdata, 0);

			function submitMessage() {
				var format = {
					'color':  $('#colorpicker').val(),
					'style':  $('#fontstyle').val(),
					'size':   $('#fontsize').val(),
					'family': $('#fontfamily').val()
				};

				var message = {
					'content': $('#submit_message_text').val(),
					'format':  format
				}
				if (!message.content.replace(/^\s+/, '').replace(/\s+$/, ''))
					return;

				$('#submit_message_text').val("");
				$.get(
					posturl + '&message=' + encodeURIComponent(JSON.stringify(message)) + '&' + buildMoreOptions(),
					function (response) {
						response = typeof response == 'object' ? response : $.getAsObject(response);
						if (!response.success) {
							alert(response.reason);
						}
					},
					'json'
				);
			}

			function kickUser(userid) {
				var message = userid;
				$.get(posturl.replace(/postMessage/, 'kick') + '&user=' + encodeURIComponent(message) + '&' + buildMoreOptions());
			}

			function kickUserOutOfSubroom(userid) {
				var message = userid;
				$.get(
					posturl.replace(/postMessage/, 'kick-sub') + '&user=' + encodeURIComponent(message) + '&' + buildMoreOptions(),
					function (response) {
						response = $.getAsObject(response);
						username = $('#chat_users').ilChatList('getDataById', userid).label;

						if (response.success == true) {
							$('#chat_messages').ilChatMessageArea(
								'addMessage',
								-1,
								{
									type:    'notice',
									message: translate('user_kicked', {user: username})
								}
							);
							$('#invite_users_container').ilChatDialog('close');

						}
					}
				);
			}

			function banUser(userid) {
				var message = userid;
				$.get(posturl.replace(/postMessage/, 'ban-active') + '&user=' + encodeURIComponent(message) + '&' + buildMoreOptions());
			}

			function handleMessage(message) {
				messageObject = (typeof message == 'object') ? message : $.getAsObject(message);

				if (typeof DEBUG != 'undefined' && DEBUG) {
					$('#chat_messages').ilChatMessageArea('addMessage', 0, {
						type:    'notice',
						message: messageObject.type
					});
					console.log(messageObject);
				}

				if ((!messageObject.sub && subRoomId) || (subRoomId && subRoomId != messageObject.sub)) {
					$('#chat_actions').addClass('chat_new_events');
					var id = typeof messageObject == 'undefined' ? 0 : messageObject.sub;
					var data = $('#private_rooms').ilChatList('getDataById', id);
					if (data) {
						data.new_events = true;
					}
				}

				switch (messageObject.type) {
					case 'user_invited':
						if (messageObject.invited == personalUserInfo.userid) {
							var room_label;
							if (messageObject.proom_id) {
								room_label = $('#private_rooms').ilChatList('getDataById', messageObject.proom_id).label;
							}
							else {
								room_label = translate('main');
							}

							if ($('#chat_users').ilChatList('getDataById', messageObject.inviter)) {
								$('#chat_messages').ilChatMessageArea('addMessage', subRoomId, {
									type:    'notice',
									message: translate('user_invited_self', {
										user: $('#chat_users').ilChatList('getDataById', messageObject.inviter).label,
										room: room_label
									})
								});
							}
						}

						break;
					case 'private_room_entered':
						var data = $('#private_rooms').ilChatList('getDataById', messageObject.sub);
						var userdata = $('#chat_users').ilChatList('getDataById', messageObject.user);
						if (data) {
							var added = usermanager.add(userdata, data.id);
							if (userdata.id == myId && added) {
								$('#chat_messages').ilChatMessageArea('addMessage', messageObject.sub || 0, {
									type:    'notice',
									message: translate('private_room_entered', {title: data.label})
								});
							}
							else if (added) {
								$('#chat_messages').ilChatMessageArea('addMessage', messageObject.sub || 0, {
									type:    'notice',
									message: translate('private_room_entered_user', {
										user:  userdata.label,
										title: data.label
									})
								});

								$('.user_' + userdata.id).show();
							}

							if ($('.online_user:visible').length == 0) {
								$('.no_users').show();
							}
							else {
								$('.no_users').hide();
							}
						}

						if (messageObject.user == personalUserInfo.userid && subRoomId != messageObject.sub) {
							$('#chat_messages').ilChatMessageArea('show', messageObject.sub, posturl);
						}

						break;
					case 'private_room_left':
						var data = $('#private_rooms').ilChatList('getDataById', messageObject.sub);
						var userdata = $('#chat_users').ilChatList('getDataById', messageObject.user);
						if (data && userdata) {
							$('#chat_messages').ilChatMessageArea('addMessage', messageObject.sub || 0, {
								type:    'private_room_left',
								message: translate('private_room_left', {user: userdata.label, title: data.label})
							});
						}
						/*
						 if (messageObject.user == myId) {
						 roomHandler.getRoom(messageObject.sub).removeClass('in_room');
						 }*/
						if (messageObject.sub && messageObject.sub == subRoomId) {
							$('#chat_users').find('.user_' + messageObject.user).hide();
						}
						usermanager.remove(messageObject.user, messageObject.sub);
						if ($('.online_user:visible').length == 0) {
							$('.no_users').show();
						}
						else {
							$('.no_users').hide();
						}
						break;
					case 'private_room_created':
						$('#chat_messages').ilChatMessageArea('addScope', messageObject.proom_id, messageObject);
						$('#private_rooms').ilChatList('add', {
							id:    messageObject.proom_id,
							label: messageObject.title,
							type:  'room',
							owner: messageObject.owner
						});
						break;
					case 'private_room_deleted':
						var data = $('#private_rooms').ilChatList('getDataById', messageObject.proom_id);
						if (data) {
							$('#chat_messages').ilChatMessageArea('addMessage', 0, {
								type:    'notice',
								message: translate('private_room_closed', {title: data.label})
							});
						}

						$('#private_rooms').ilChatList('removeById', messageObject.proom_id);

						if (messageObject.proom_id == subRoomId) {
							subRoomId = 0;
							$('#chat_messages').ilChatMessageArea('show', 0);
						}
						break;
					case 'message':
						$('#chat_messages').ilChatMessageArea('addMessage', messageObject.sub || 0, messageObject);
						break;
					case 'disconnected':
						$(messageObject.users).each(function (i) {
							var data = $('#chat_users').ilChatList('getDataById', messageObject.users[i].id);
							$('#chat_messages').ilChatMessageArea('addMessage', 0, {
								login:     data.label,
								timestamp: messageObject.timestamp,
								type:      'disconnected'
							});
							$('#chat_users').ilChatList('removeById', messageObject.users[i].id);
							usermanager.remove(messageObject.users[i].id, 0);
						});
						break;
					case 'connected':
						$(messageObject.users).each(function (i) {
							var data = {
								id:    this.id,
								label: this.login,
								type:  'user'
							};
							$('#chat_users').ilChatList('add', data);

							usermanager.add(data, 0);
							if (subRoomId) {
								$('.user_' + this.id).hide();
							}


							$('#chat_messages').ilChatMessageArea('addMessage', 0, {
								login:     data.label,
								timestamp: messageObject.timestamp,
								type:      'connected'
							});
						});
						break;
					case 'userjustkicked':
						// Handles kicks and bans of private rooms and the main room
						var kickeduser = $('#chat_users').ilChatList('getDataById', messageObject.user);
						usermanager.remove(messageObject.user, messageObject.sub);
						if (messageObject.user == myId) {
							var data = $('#private_rooms').ilChatList('getDataById', messageObject.sub);

							$('#chat_messages').ilChatMessageArea('show', 0);
							$('#chat_messages').ilChatMessageArea('addMessage', 0, {
								type:    'notice',
								message: translate('kicked_from_private_room', {
									title: data.label
								})
							});

							$('#private_rooms').ilChatList('removeById', messageObject.sub);
						} else if (messageObject.sub == subRoomId) {
							if (typeof messageOptions != 'undefined' && messageOptions.recipient && messageOptions.recipient == messageObject.user) {
								setRecipientOptions(false, 1);
							}

							if (typeof kickeduser != "undefined") {
								$('#chat_messages').ilChatMessageArea('addMessage', messageObject.sub, {
									type:    'notice',
									message: translate('user_kicked', {
										user: kickeduser.label
									})
								});
							}

							if (!subRoomId) {
								$('#chat_users').ilChatList('removeById', messageObject.user);
							} else {
								$('#chat_users').find('.user_' + messageObject.user).hide();
							}

							if ($('.online_user:visible').length == 0) {
								$('.no_users').show();
							}
							else {
								$('.no_users').hide();
							}
						}
						break;

					case 'clear':
						$('#chat_messages').ilChatMessageArea('clearMessages', messageObject.sub);
						$('#chat_messages').ilChatMessageArea('addMessage', messageObject.sub, {
							type:    'notice',
							message: translate('history_has_been_cleared')
						});
						break;

					case 'notice':
						$('#chat_messages').ilChatMessageArea('addMessage', messageObject.sub, {
							type:    'notice',
							message: messageObject.message
						});
						break;

					default:
						break;
				}
			}

			var last_poll_position = -1;

			function poll() {
				$.get(
					polling_url,
					{
						pos: last_poll_position,
						id:  session_id
					},
					function (response) {
						if (!response || !response.subscribed) {
							window.location.href = initial.redirect_url;
							return false;
						}
						if (response && response.messages) {
							$(response.messages).each(function (i) {
								handleMessage(response.messages[i]);
							});
							last_poll_position = response.next_position;
						}
						if ($('#chat_auto_scroll').is(':checked')) {
							$('#chat_messages').scrollTop(1000000);
						}
						window.setTimeout(poll, 500);
					},
					'jsonp'
				);
			}

			$('#chat_actions').click(function (e) {
				$(this).removeClass('chat_new_events');
				e.preventDefault();
				e.stopPropagation();
				var menuEntries = [];
				var room;

				if (subRoomId) {
					menuEntries.push(
						{
							label:    translate('leave'),
							callback: function () {
								$.get(
									posturl.replace(/postMessage/, 'privateRoom-leave') + '&sub=' + room.id,
									function (response) {
										if (typeof response != 'object') {
											response = $.getAsObject(response);
										}

										if (!response.success) {
											alert(response.reason);
										}

										$('#chat_messages').ilChatMessageArea('show', 0);

									},
									'json'
								)
							}
						}
					);
				}

				if (subRoomId && ((room = $('#private_rooms').ilChatList('getDataById', subRoomId)).owner == personalUserInfo.userid) || personalUserInfo.moderate == true) {

					menuEntries.push(
						{
							label:      translate('delete_private_room'),
							callback:   function () {
								$.get(
									posturl.replace(/postMessage/, 'privateRoom-delete') + '&sub=' + room.id,
									function (response) {
										if (typeof response != 'object') {
											response = $.getAsObject(response);
										}

										if (!response.success) {
											alert(response.reason);
										}
									},
									'json'
								)
							},
							permission: ['moderator', 'owner']
						}
					)
				}
				if (initial.private_rooms_enabled) {
					menuEntries.push(
						{
							label:    translate('create_private_room'),
							callback: function () {
								$('#create_private_room_dialog').ilChatDialog({
									title:          translate('create_private_room'),
									positiveAction: function () {
										if ($('#new_room_name').val().replace(/^\s*/, '').replace(/\s*$/) == '') {
											alert(translate('empty_name'));
											return false;
										}
										else {
											$.get(
												posturl.replace(/postMessage/, 'privateRoom-create') + '&title=' + encodeURIComponent($('#new_room_name').val()),
												function (response) {
													response = typeof response == 'object' ? response : $.getAsObject(response);
													if (!response.success) {
														alert(response.reason);
													}
												},
												'json'
											);
										}
									}
								});
							}
						}
					);
				}
				if (!subRoomId || (subRoomId && ((room = $('#private_rooms').ilChatList('getDataById', subRoomId)).owner == personalUserInfo.userid) || personalUserInfo.moderate == true)) {
					menuEntries.push(
						{
							label:    translate('invite_users'),
							callback: function () {

								var invitationChangeTimeout;
								$('#invite_users_container')
									.ilChatDialog({
										title:            translate('invite_users'),
										close:            function () {
											if (invitationChangeTimeout) {
												window.clearInterval(invitationChangeTimeout);
												invitationChangeTimeout = undefined;
											}
										},
										positiveAction:   function () {

										},
										disabled_buttons: ['ok']
									});

								var sendInvitation = function (user, type, username) {
									$.get(
										posturl.replace(/postMessage/, 'inviteUsersToPrivateRoom-' + type) + (subRoomId ? ('&sub=' + subRoomId) : '') + '&users=' + user,
										function (response) {
											response = $.getAsObject(response);
											if (response.success == true) {
												$('#chat_messages').ilChatMessageArea('addMessage', -1, {
													type:    'notice',
													message: translate('user_invited', {user: username})
												});
												$('#invite_users_container').ilChatDialog('close');
											}
											else {
												$('#chat_messages').ilChatMessageArea('addMessage', -1, {
													type:    'notice',
													message: 'Not allowed! Not owner of private room (Beta status)!'
												});
												$('#invite_users_container').ilChatDialog('close');
											}
										}
									);
								}

								$('#invite_users_global').click(function () {
									$('#invite_user_text_wrapper').show();
									$('#invite_users_available').children().remove();
								});

								$('#invite_users_in_room').click(function () {
									$('#invite_user_text_wrapper').hide();

									$('#invite_users_available').children().remove();
									var show = false;
									$.each($('#chat_users').ilChatList('getAll'), function () {
										var id = this.id;

										if (!isIdInArray(id, usermanager.usersinroom(subRoomId))) {
											$('#invite_users_available').append(
												$('<li class="invite_user_line_id invite_user_line"></li>')
													.append($('<a href="#"></a>')
														.text(this.label)
														.click(function (e) {
															e.preventDefault();
															e.stopPropagation();
															sendInvitation(id, 'byId', $(this).text());
														})
												)
											);
											show = true;
										}
									});
									if (show == false) {
										$('#invite_users_in_room').hide();
										$('#radioText').hide();
										$('input[name=invite_users_type]').removeAttr('checked');
										setTimeout(function () {
											$('#invite_users_global').click();
										}, 300);

									}
									else {
										$('#invite_users_in_room').show();
										$('#radioText').show();
									}

								});

								$('#invite_users_in_room').click();

								var cb;
								if (invitationChangeTimeout) {
									window.clearTimeout(invitationChangeTimeout);
								}

								var oldValue = $('#invite_user_text').val();
								invitationChangeTimeout = window.setTimeout(cb = function () {
									if ($('#invite_user_text').val() != oldValue && $('#invite_user_text').val().length > 2) {
										oldValue = $('#invite_user_text').val();
										$.get(
											posturl.replace(/postMessage/, 'inviteUsersToPrivateRoom-getUserList') + '&q=' + $('#invite_user_text').val(),
											function (response) {
												response = $.getAsObject(response);
												$('#invite_users_available').html('');

												if (response && $(response.items).size()) {
													$(response.items).each(function () {
														var login = this.value;
														var publicName = this.label;
														$('<li class="invite_user_line_login invite_user_line"></li>')
															.append($('<a href="#"></a>')
																.text(publicName)
																.click(function (e) {
																	e.preventDefault();
																	e.stopPropagation();
																	sendInvitation(login, 'byLogin', login);
																})
														).appendTo($('#invite_users_available'));
													});
												}
												invitationChangeTimeout = window.setTimeout(cb, 500);
											},
											'json');
									}
									else {
										if (!$('#invite_user_text').val().length) {
											$('#invite_users_available').html('');
										}
										invitationChangeTimeout = window.setTimeout(cb, 300);
									}
								}, 500);

							}
						}
					);
				}
				if (personalUserInfo.moderator) {
					menuEntries.push(
						{
							label:    translate('clear_room_history'),
							callback: function () {
								if (window.confirm(translate('clear_room_history_question'))) {

									$.get(
										posturl.replace(/postMessage/, 'clear') + (subRoomId ? ('&sub=' + subRoomId) : ''),
										function (response) {
											response = typeof response == 'object' ? response : $.getAsObject(response);
											if (!response.success) {
												alert(response.reason);
											}
										},
										'json'
									);
								}
							}
						}
					);
				}
				if (initial.private_rooms_enabled) {
					menuEntries.push({separator: true});
					var rooms = [
						{
							label: translate('main'),
							id:    0,
							owner: 0,
							addClass: 'room_0' + (!subRoomId ? ' in_room' : '')
						}
					].concat($('#private_rooms').ilChatList('getAll'));

					rooms.sort(function (a, b) {

						if (a.id == 0) {
							return -1;
						}
						else if (b.id == 0) {
							return 1;
						}

						return a.label < b.label ? -1 : 1;
					});


					$.each(rooms, function () {
						var room = this;
						var classes = ['room_' + room.id];

						if (subRoomId == room.id) {
							classes.push('in_room');
						}
						if (room.new_events) {
							classes.push('chat_new_events');
						}

						menuEntries.push({
							label:    this.label,
							icon: 'templates/default/images/' + (!room.id ? 'icon_chtr.svg' : 'icon_chtr.svg'),
							addClass: classes.join(' '),
							callback: function () {
								if (!room.id) {
									$('#chat_messages').ilChatMessageArea('show', 0);
									return;
								}
								else if (subRoomId == room.id) {
									return;
								}
								room.new_events = false;
								$.get(
									posturl.replace(/postMessage/, 'privateRoom-enter') + '&sub=' + room.id,
									function (response) {
										if (typeof response != 'object') {
											response = $.getAsObject(response);
										}

										if (!response.success) {
											alert(response.reason);
										}

										subRoomId = room.id;

										$('#chat_messages').ilChatMessageArea('show', room.id, posturl);

										if (subRoomId) {/*
										 $('#chat_users').find('.online_user').hide();
										 usermanager.clear(room.id); 
										 $.get(
										 posturl.replace(/postMessage/, 'privateRoom-listUsers') + '&sub=' + room.id,

										 function(response)
										 {
										 response = typeof response == 'object' ? response : $.getAsObject(response);

										 $.each(response, function() {
										 $('#chat_users').find('.user_' + this).not('.hidden_entry').show();
										 userdata = $('#chat_users').ilChatList('getDataById', this);
										 usermanager.add(userdata, room.id);
										 });

										 if (!$('#chat_messages').ilChatMessageArea('hasContent', room.id)) {
										 $('#chat_messages').ilChatMessageArea('addMessage', room.id, {
										 type: 'notice',
										 message: translate('private_room_entered', {title: room.label})
										 });
										 }
										 },
										 'json'
										 );*/

										}
										else {
											$('#chat_users').find('.online_user').not('.hidden_entry').show();
										}
									},
									'json'
								)
							}
						});
					});
				}
				$(this).ilChatMenu('show', menuEntries, true);
			});

			window.setTimeout(function () {
				$('#chat_messages').ilChatMessageArea('addMessage', 0, {
					type:    'notice',
					message: translate('welcome_to_chat')
				});
				poll();
			}, 10);

		}
	}(jQuery)
});