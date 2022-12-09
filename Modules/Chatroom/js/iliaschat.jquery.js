(function (root, scope, factory) {
	scope.Chatroom = factory(root, root.jQuery);
}(window, il, function init(root, $) {
	"use strict";

	let room,
		initial,
		_scope,
		personalUserInfo,
		redirectUrl,
		translation,
		posturl,
		serverConnector,
		logger,
		messageOptions,
		smileys,
		iliasConnector, 
		chatActions;

	jQuery.fn.extend({
		insertAtCaret: function (value) {
			return this.each(function (i) {
				if (document.selection) {
					//For browsers like Internet Explorer
					this.focus();
					const sel = document.selection.createRange();
					sel.text = value;
					this.focus();
				} else if (this.selectionStart || this.selectionStart == '0') {
					//For browsers like Firefox and Webkit based
					const startPos = this.selectionStart,
						endPos = this.selectionEnd,
						scrollTop = this.scrollTop;

					this.value = this.value.substring(0, startPos) + value + this.value.substring(endPos, this.value.length);
					this.focus();
					this.selectionStart = startPos + value.length;
					this.selectionEnd = startPos + value.length;
					this.scrollTop = scrollTop;
				} else {
					this.value += value;
					this.focus();
				}
			});
		}
	});

	function closeMenus() {
		$('.menu').hide();
	}

	function formatToTwoDigits(nr) {
		nr = "" + nr;
		while (nr.length < 2) {
			nr = "0" + nr;
		}

		return nr;
	}

	function translate(key, args) {
		return translation.translate(key, args);
	}

	function formatISOTime(time) {
		let format = translation.translate("timeformat");
		const date = new Date(time);

		format = format.replace(/H/, formatToTwoDigits(date.getHours()));
		format = format.replace(/i/, formatToTwoDigits(date.getMinutes()));
		format = format.replace(/s/, formatToTwoDigits(date.getSeconds()));

		return format;
	}

	function formatISODate(time) {
		let format = translation.translate("dateformat");
		const date = new Date(time);

		format = format.replace(/Y/, date.getFullYear());
		format = format.replace(/m/, formatToTwoDigits(date.getMonth() + 1));
		format = format.replace(/d/, formatToTwoDigits(date.getDate()));

		return format;
	}

	function isIdInArray(id, objects) {
		for (let i in objects) {
			if (typeof objects[i] != 'undefined' && typeof objects[i].id != 'undefined' && objects[i].id == id) {
				return true;
			}
		}

		return false;
	}

	const Logger = function Logger() {
		this.logServerResponse = function (message) {
			_log('Server-Response', message);
		};

		this.logServerRequest = function (message) {
			_log('Server-Request', message);
		};

		this.logILIASResponse = function (message) {
			_log('ILIAS-Response', message);
		};

		this.logILIASRequest = function (message) {
			_log('ILIAS-Request', message);
		};

	function _log(type, message) {
		console.log(type, message);
	}
};

	/**
	 * This class translates all translation key to language text.
	 * It is only able to translate thouse keys, which are delivered into _lang via the constructor
	 * @param {array} _lang
	 * @constructor
	 */
	const Translation = function Translation(_lang) {
		/**
		 * 
		 * @param {string} key
		 * @param {object} args
		 * @returns {string}
		 */
		this.translate = function (key, args) {
			if (_lang[key]) {
				let lng = _lang[key];

				if (args !== undefined) {
					for (let i in args) {
						lng = lng.replace(new RegExp('#' + i + '#', 'g'), args[i]);
					}
				}

				return lng;
			}

			return '#' + key + '#';
		}
	};

	const ProfileImageLoader = function (_userId, _onFinishCallback) {
		const requestUserImages = function (userIds) {
			let difference = [];

			$.grep(userIds, function (el) {
				if ($.inArray(el, Object.keys(ProfileImageLoader._imagesByUserId)) === -1) {
					difference.push(el);
				}
			});

			if (difference.length > 0) {
				$.get(
					initial.profile_image_url + '&usr_ids=' + userIds.join(','),
					function (response) {
						$.each(response, function (id, item) {
							const img = new Image();
							img.src = item.profile_image;

							ProfileImageLoader._imagesByUserId[id] = img;
						});
						_onFinishCallback()
					},
					'json'
				);
			} else {
				_onFinishCallback()
			}
		};

		requestUserImages(_userId);

		this.getProfileImage = function (_userId) {
			if (ProfileImageLoader._imagesByUserId.hasOwnProperty(_userId)) {
				return ProfileImageLoader._imagesByUserId[_userId];
			}

			const img = new Image();
			img.src = initial.no_profile_image_url;

			return img;
		};
	};
	ProfileImageLoader._imagesByUserId = {};

	/**
	 * This class renders the smiley selection for ChatActions.
	 * It also replaces all smileys in a chat messages.
	 *
	 * @params {array} _smileys
	 * @constructor
	 */
	const Smileys = function Smileys(_smileys) {
		/**
		 * Sets smileys into text
		 *
		 * @param {string} message
		 * @returns {string}
		 */
		this.replace = function (message) {
			if (typeof _smileys == "string") {
				return message;
			}

			for (let i in _smileys) {
				while (message.indexOf(i) != -1) {
					message = message.replace(i, '<img src="' + _smileys[i] + '" />');
				}
			}

			return message;
		};

		this.render = function () {
			if (typeof _smileys == "object") {
				if (_smileys.length == 0) {
					return;
				}
				// Emoticons
				const $emoticons_flyout_trigger = $('<a></a>'),
					$emoticons_flyout = $('<div id="iosChatEmoticonsPanelFlyout"></div>'),
					$emoticons_panel = $('<div id="iosChatEmoticonsPanel"></div>')
					.append($emoticons_flyout_trigger)
					.append($emoticons_flyout);

				$("#submit_message_text").css("paddingLeft", "25px").after($emoticons_panel);

				$emoticons_panel.css("top", "3px");

				const $emoticons_table = $("<table></table>"),
					emoticonMap = new Object();

				let $emoticons_row = null,
					cnt = 0;

				for (let i in _smileys) {
					let $emoticon;

					if (emoticonMap[_smileys[i]]) {
						$emoticon = emoticonMap[_smileys[i]];
					} else {
						if (cnt % 6 == 0) {
							$emoticons_row = $("<tr></tr>");
							$emoticons_table.append($emoticons_row);
						}

						$emoticon = $('<img src="' + _smileys[i] + '" alt="" title="" />');
						$emoticon.data("emoticon", i);
						$emoticons_row.append($('<td></td>').append($('<a></a>').append($emoticon)));

						emoticonMap[_smileys[i]] = $emoticon;
						++cnt;
					}

					$emoticon.attr({
						alt: [$emoticon.attr('alt').toString(), i].join(' '),
						title: [$emoticon.attr('title').toString(), i].join(' ')
					});
				}
				$emoticons_flyout.append($emoticons_table);

				$emoticons_flyout_trigger.on('click', function (e) {
					$emoticons_flyout.toggle();

					if ($(this).hasClass("active")) {
						$(this).removeClass("active");
					} else {
						$(this).addClass("active");
					}
				});

				$emoticons_panel.on('clickoutside', function (event) {
					if ($emoticons_flyout_trigger.hasClass("active")) {
						$emoticons_flyout_trigger.click();
					}
				});

				$("#iosChatEmoticonsPanelFlyout a").click(function () {
					$emoticons_flyout_trigger.click();
					$("#submit_message_text").insertAtCaret($(this).find('img').data("emoticon"));
				});
			}
		};
	};

	/**
	 * This class renders the chat gui and manages all gui actions.
	 *
	 * @param {Translation} _translation
	 * @constructor
	 */
	const GUI = function GUI(_translation) {
		let _prevSize = {width: 0, height: 0},
			_$anchor = $('#chat_messages');

		this.renderHeaderAndActionButton = function () {
			$('<div id="tttt" style="white-space:nowrap;"></div>')
				.append($('#chat_actions_wrapper'))
				.insertBefore($('.il_HeaderInner').find('h1'));
		};

		/**
		 * @param {number} interval
		 */
		this.resizeChatWindowInInterval = function (interval) {
			window.setInterval(function () {
				const body = $('body'),
					currentSize = {width: body.width(), height: body.height()};

				if (currentSize.width != _prevSize.width || currentSize.height != _prevSize.height) {
					$('#chat_sidebar_wrapper').height(
						$('#chat_sidebar').parent().height() - $('#chat_sidebar_tabs').height()
					);
					_prevSize = {width: body.width(), height: body.height()};
				}
			}, interval);
		};

		this.initChatMessageArea = function (state) {
			_$anchor.ilChatMessageArea(state);
		};

		this.addChatMessageArea = function (title, owner) {
			_$anchor.ilChatMessageArea('addScope', {
				title: title,
				owner: owner
			});
		};

		this.showChatMessageArea = function (scope) {
			_$anchor.ilChatMessageArea('show', scope);
		};

		this.addMessage = function (messageObject) {
			_$anchor.ilChatMessageArea('addMessage', messageObject);
		};

	this.addTypingInfo = function(messageObject, text) {
		_$anchor.ilChatMessageArea('addTypingInfo', messageObject, text);
	};
};

	/**
	 * This class manages all users for all rooms.
	 *
	 * @constructor
	 */
	const UserManager = function UserManager() {

		/**
		 * Stores all users for this room.
		 *
		 * @type {Array}
		 * @private
		 */
		let _usersByRoom = [];

		/**
		 * Adds a user to the delivered room.
		 *
		 * @param {JSON} userdata
		 * @returns {boolean}
		 */
		this.add = function (userdata) {
			for (let i in _usersByRoom) {
				const current = _usersByRoom[i];
				if (current.id == userdata.id) {
					_usersByRoom[i].label = userdata.label;
					return false;
				}
			}

			_usersByRoom.push(userdata);

			return true;
		};

		/**
		 * Remove a delivered user from the delivered room.
		 *
		 * @param {number} userId
		 */
		this.remove = function (userId) {
			for (let i in _usersByRoom) {
				const user = _usersByRoom[i];
				if (user.id == userId) {
					delete _usersByRoom[i];
				}
			}
		};

		/**
		 * Get all users of this room.
		 *
		 * @returns {Array}
		 */
	        this.getUsersInRoom = function () {
			return _usersByRoom;
		};

		/**
		 * Removes all users from the delivered room.
		 *
		 * @param {string} roomId
		 */
		this.clear = function () {
			_usersByRoom = [];
		};
	};

	/**
	 * This class renders all available action for a user in a chat room.
	 *
	 * @param {string} selector
	 * @param {Translation} _translator
	 * @param {ILIASConnector} _connector
	 * @constructor
	 */
	const ChatUsers = function ChatUsers(selector, _translator, _connector) {

		let _$anchor = $(selector);

		/**
		 * Initializes all available action.
		 */
		this.init = function () {
			const actions = [];

			actions.push(_addAddressAction());
			actions.push(_addWhisperAction());

			if (personalUserInfo.moderator == true) {
				actions.push(_addKickAction());
			}
			if (personalUserInfo.moderator == true) {
				actions.push(_addBanAction());
			}
			_$anchor.ilChatUserList(actions);
		};

		/**
		 * Adds an action to address a chat message to the selected user
		 *
		 * @returns {{label: string, callback: callback}}
		 * @private
		 */
		function _addAddressAction() {
			return {
				label: _translator.translate('address'),
				callback: function () {
					setRecipientOptions(this.id, 1); // @TODO setRecipientOptions(this.id, 1);
				}
			};
		}

		/**
		 * Adds an action to send a private message to one user in the current room.
		 *
		 * @returns {{label: string, callback: callback}}
		 * @private
		 */
		function _addWhisperAction() {
			return {
				label: _translator.translate('whisper'),
				callback: function () {
					setRecipientOptions(this.id, 0); // @TODO setRecipientOptions(this.id, 0);
				}
			};
		}

		/**
		 * Adds an action to kick a user from the current room.
		 *
		 * @returns {{label: string, callback: callback, permission: string[]}}
		 * @private
		 */
		function _addKickAction() {
			return {
				label: _translator.translate('kick'),
				callback: function () {
					if (confirm(_translator.translate('kick_question'))) {
						_connector.kick(this.id);
					}
				},
				permission: ['moderator', 'owner']
			};
		}

		/**
		 * Adds an action to ban a user from the current room.
		 *
		 * @returns {{label: string, callback: callback, permission: string[]}}
		 * @private
		 */
		function _addBanAction() {
			return {
				label: _translator.translate('ban'),
				callback: function () {
					if (confirm(_translator.translate('ban_question'))) {
						_connector.ban(this.id);
					}
				},
				permission: ['moderator']
			};
		}
	};

	/**
	 * This class renders all available actions for the chat into a dropdown.
	 *
	 * @param {string} selector
	 * @param {Translation} _translation
	 * @param {ILIASConnector} _connector
	 * @oaram {UserManager} userManager
	 */
	const ChatActions = function (selector, _translation, _connector, userManager) {

		let _$anchor = $(selector), _menuEntries = [];


		// Done
		/**
		 * Setup ChatActions
		 */
		this.init = function () {
			_$anchor.click(function (e) {
				e.preventDefault();
				e.stopPropagation();
				_resetEntries();

				$(this).removeClass('chat_new_events');
				_addInviteSubRoomAction();
				_addClearHistoryAction();

				$(this).ilChatMenu('show', _menuEntries, true);
			});
		};

		/**
		 * Clears all menu entries
		 *
		 * @private
		 */
		function _resetEntries() {
			_menuEntries = [];
		}

		/**
		 * Adds an action to invite a user to a sub room.
		 * Invitation can be done by user id or user login name.
		 * Users are able to invite users subscribed to the main room or search for existing users in ILIAS.
		 *
		 * @TODO Check if user is in subRoom and has permission to invite users to private room.
		 *
		 * @private
		 */
		function _addInviteSubRoomAction() {
		    _menuEntries.push(
			{
			    label: _translation.translate('invite_users'),
			    callback: function () {
				$('#invite_users_container').ilChatDialog({
				    title: _translation.translate('invite_users'),
				    close: function () {
					$('#invite_user_text_wrapper').val('');
				    },
				    positiveAction: function () {
				    },
				    disabled_buttons: ['ok']
				});

				$('#invite_users_global').click(function () {
				    $('#invite_users_available').children().remove();
				    $('#invite_user_text_wrapper').show();

				    $('#invite_user_text').keyup(function () {
					if ($(this).val().length > 2) {
					    $.get(
						posturl.replace(/postMessage/, 'inviteUsersToPrivateRoom-getUserList') + '&q=' + $('#invite_user_text').val(),
						function (response) {
						    $('#invite_users_available').children().remove();
						    $(response.items).each(function () {
							const usersInRoom = userManager.getUsersInRoom();
							if (!isIdInArray(this.id, usersInRoom)) {
							    _addUserForInvitation(this.value, 'byLogin', this.value);
							}
						    });
						},
						'json'
					    );
					} else {
					    $('#invite_users_available').children().remove();
					}
				    });
				});

				$('#invite_users_in_room').click(function () {
				    const availableUsers = $('#invite_users_available');
				    $(availableUsers).children().remove();
				    $('#invite_user_text_wrapper').hide();

				    // load all User connected to main room.
				    const chatUsers = userManager.getUsersInRoom(),
					  usersInRoom = userManager.getUsersInRoom();

				    $(chatUsers).each(function () {
					if (!isIdInArray(this.id, usersInRoom)) {
					    _addUserForInvitation(this.id, 'byId', this.label);
					}
				    });

				    if ($(availableUsers).children().length == 0) {
					$('#invite_users_in_room').remove();
					$('#radioText').remove();
					$('#invite_users_global').prop('checked', 'checked').click();
				    }
				}).click();
			    }
			}
		    );
		    return room;
		}

		/**
		 * Adds an action to clear the current room chat history.
		 *
		 * @TODO Check if user has permission to clear history
		 *
		 * @private
		 */
		function _addClearHistoryAction() {
			if (personalUserInfo.moderator) {
				_menuEntries.push({
					label: _translation.translate('clear_room_history'),
					callback: function () {
						if (confirm(_translation.translate('clear_room_history_question'))) {
							_connector.clear();
						}
					}
				});
			}
		}

		/**
		 * Renders an html string to show users which are able to be invited to the sub room.
		 *
		 * @param {string} userValue
		 * @param {string} invitationType
		 * @param {string} label
		 * @private
		 */
		function _addUserForInvitation(userValue, invitationType, label) {
			const link = $('<a></a>')
				.prop('href', '#')
				.text(label)
				.click(function (e) {
					e.preventDefault();
					e.stopPropagation();
					_connector.inviteToPrivateRoom(userValue, invitationType);
				});
			const line = $('<li></li>')
				.addClass('invite_user_line_id')
				.addClass('invite_user_line')
				.append(link);

			$('#invite_users_available').append(line);
		}
	};

const ChatTypingUsersTextGeneratorFactory = (function () {
	let instances = {};

	/**
	 *
	 * @param {String} conversationId
	 * @constructor
	 */
	function TypingUsersTextGenerator(conversationId) {
		this.conversationId = conversationId;
		this.typingMap = new Map();
	}

	/**
	 *
	 * @param {Number} id
	 * @param {String} username
	 */
	TypingUsersTextGenerator.prototype.addTypingSubscriber = function(id, username) {
		if (!this.typingMap.has(id)) {
			this.typingMap.set(id, username);
		}
	}

	/**
	 *
	 * @param {Number} id
	 * @param {String} username
	 */
	TypingUsersTextGenerator.prototype.removeTypingSubscriber = function(id, username) {
		if (this.typingMap.has(id)) {
			this.typingMap.delete(id);
		}
	};

	/**
	 *
	 * @param {il.Language} language
	 * @returns {string}
	 */
	TypingUsersTextGenerator.prototype.text = function ( language) {
		const names = Array.from(this.typingMap.values());

		if (names.length === 0) {
			return '';
		} else if (1 === names.length) {
			return language.txt("chat_user_x_is_typing", names[0]);
		}

		return language.txt("chat_users_are_typing");
	};

	/**
	 *
	 * @param {String} conversationId
	 * @returns {TypingUsersTextGenerator}
	 */
	function createInstance(conversationId) {
		return new TypingUsersTextGenerator(conversationId);
	}

	return {
		/**
		 * @param {String} conversationId
		 * @returns {TypingUsersTextGenerator}
		 */
		getInstance: function (conversationId) {
			if (!instances.hasOwnProperty(conversationId)) {
				instances[conversationId] = createInstance(conversationId);
			}
			return instances[conversationId];
		}
	};
})();

const ChatTypingBroadcasterFactory = (function () {
	let instances = {}, ms = 5000;

	/**
	 *
	 * @param {Function} onTypingStarted
	 * @param {Function} onTypeingStopped
	 * @constructor
	 */
	function TypingBroadcaster(onTypingStarted, onTypingStopped) {
		this.is_typing = false;
		this.timer = 0;
		this.onTypingStarted = onTypingStarted;
		this.onTypingStopped = onTypingStopped;
	}

	TypingBroadcaster.prototype.release = function() {
		if (this.is_typing) {
			window.clearTimeout(this.timer);
			this.onTimeout();
		}
	}

	TypingBroadcaster.prototype.onTimeout = function() {
		window.clearTimeout(this.timer);
		this.is_typing = false;
		this.onTypingStopped.call();
	};

	TypingBroadcaster.prototype.registerTyping = function() {
		if (this.is_typing) {
			window.clearTimeout(this.timer);
			this.timer = window.setTimeout(this.onTimeout.bind(this), ms);
		} else {
			this.is_typing = true;
			this.onTypingStarted.call();
			this.timer = window.setTimeout(this.onTimeout.bind(this), ms);
		}
	};

	/**
	 *
	 * @param {String} scopeId
	 * @param {Function} onTypingStarted
	 * @param {Function} onTypingStopped
	 * @returns {TypingBroadcaster}
	 */
	function createInstance(scopeId, onTypingStarted, onTypingStopped) {
		return new TypingBroadcaster(onTypingStarted, onTypingStopped);
	}

	return {
		/**
		 * @param {String} scopeId
		 * @param {Function} onTypingStarted
		 * @param {Function} onTypingStopped
		 * @returns {TypingBroadcaster}
		 */
		getInstance: function (scopeId, onTypingStarted, onTypingStopped) {
			if (!instances.hasOwnProperty(scopeId)) {
				instances[scopeId] = createInstance(scopeId, onTypingStarted, onTypingStopped);
			}
			return instances[scopeId];
		},
		releaseAll: function () {
			for (let conversationId in instances) {
				if (instances.hasOwnProperty(conversationId)) {
					instances[conversationId].release();
				}
			}
		}
	};
})();

/**
 * This class handles responses of all asynchronous request done by ILIASConnector.
 * It has to be passed to the ILIASConnector instance.
 *
 * @constructor
 */
var ILIASResponseHandler = function ILIASResponseHandler() {
		/**
		 * Handles the response of a leavePrivateRoom request.
		 * Shows the related ilChatMessageArea
		 *
		 * @param {{}} response
		 */
		this.leavePrivateRoom = function (response) {
			if (!_validate(response)) {
				return;
			}

			$('#chat_messages').ilChatMessageArea('show');
		};

		/**
		 * Handles the response of an inviteToPrivateRoom request.
		 * It closes the invitation dialog.
		 *
		 * @param {{}} response
		 */
		this.inviteToPrivateRoom = function (response) {
			if (!_validate(response)) {
				return;
			}

			$('#invite_users_container').ilChatDialog('close');
		};

		/**
		 * Default handler for an ILIAS request. It just validates the response.
		 *
		 * @param {{}} response
		 *
		 * @return {boolean}
		 */
		this.default = function (response) {
			logger.logILIASResponse('default');
			return _validate(response);
		};

		/**
		 * Checks if the request was successfully. Unless it displays an error message as alert.
		 *
		 * @param {{success: boolean, reason: string}} response
		 * @returns {boolean}
		 * @private
		 */
		function _validate(response) {
			if (!response.success) {
				alert(response.reason);
				return false;
			}
			return true;
		}
	};

	/**
	 * This class connects the client to the related ILIAS environment. Communication is handled by sending
	 * JSON requests. The response of each request is handled through callbacks delivered by the ILIASResponseHandler
	 * which is passed through the constructor
	 *
	 * @param {string} _postUrl
	 * @param {ILIASResponseHandler} responseHandler
	 * @constructor
	 */
	const ILIASConnector = function (_postUrl, responseHandler) {

		let _self = this;

		/**
		 * Sends a heartbeat to ILIAS in a delivered interval. It is used to keep the session for an ILIAS user open.
		 *
		 * @param {number} interval
		 */
		this.heartbeatInterval = function (interval) {
			window.setInterval(function () {
				_sendRequest('poll', {}, function(response) {});
			}, interval);
		};

		/**
		 * Sends a request to ILIAS to leave a private room.
		 */
		this.leavePrivateRoom = function () {
			logger.logILIASRequest('leavePrivateRoom');
			_sendRequest('privateRoom-leave', {}, responseHandler.leavePrivateRoom);
		};

		/**
		 * Sends a request to ILIAS to invite a specific user to a private room.
		 * The invitation can be done by two types
		 *    1. byId
		 *    2. byLogin
		 *
		 * @param {string} userValue
		 * @param {string} invitationType
		 */
		this.inviteToPrivateRoom = function (userValue, invitationType) {
			_sendRequest('inviteUsersToPrivateRoom-' + invitationType, {
				user: userValue
			}, responseHandler.inviteToPrivateRoom);
		};

		/**
		 * Sends a request to ILIAS to clear the chat history
		 */
		this.clear = function () {
			_sendRequest('clear', {}, responseHandler.default);
		};

		/**
		 * Sends a request to ILIAS to kick a user from a specific room. The room can either be a private or the main room.
		 *
		 * @param {number} userId
		 */
		this.kick = function (userId) {
			_sendRequest('kick', {user: userId}, responseHandler.default);
		};

		/**
		 * Sends a request to ILIAS to ban a user from a specific room. The room can either be a private or the main room.
		 *
		 * @param {number} userId
		 */
		this.ban = function (userId) {
			_sendRequest('ban-active', {user: userId}, responseHandler.default);
		};

		/**
		 * Sends a asynchronously JSON request to ILIAS.
		 *
		 * @param {string} action
		 * @param {{}} params
		 * @param {function} responseCallback
		 * @private
		 */
		function _sendRequest(action, params, responseCallback) {
			$.get(_postUrl.replace(/postMessage/, action) + _generateParamsString(params), function (response) {
				response = $.getAsObject(response);
				responseCallback(response);
			}, 'JSON');
		}

		/**
		 * Generates request parameter string for an asynchronous request.
		 *
		 * @param {Array} params
		 * @returns {string}
		 * @private
		 */
		function _generateParamsString(params) {
			let string = '';
			for (let key in params) {
				string += '&' + key + '=' + encodeURIComponent(params[key]);
			}
			return string;
		}
	};

	/**
	 * This class connects the client to the related chat server. Communication is handled through websockets as far as
	 * it is supported by the users browser. Otherwise it uses polling method to communicate. Messages are send through
	 * `socket.emit`. Messages are received through `socket.on`. There can be 3 types of messages.
	 *    1. Text messages which are send by the chat users
	 *    2. Notification messages. This are informational messages which are triggered by the system.
	 *    3. Action messages. This messages triggers action which have to be executed in the client. This messages are
	 *        triggered by the System.
	 *
	 * @param url
	 * @param scope
	 * @param user
	 * @param {UserManager} userManager
	 * @param {GUI} gui
	 * @constructor
	 */
	const ServerConnector = function ServerConnector(url, scope, user, userManager, gui, subdirectory) {

		let _socket;

		/**
		 * Setup server connector
		 */
		this.init = function () {
			_socket = io.connect(url, {path: subdirectory});

		_socket.on('message', _onMessage);
		_socket.on('connect', function(){
			_socket.emit('login', user.login, user.id);
		});
		_socket.on('user_invited', _onUserInvited);
		_socket.on('private_room_entered', _onPrivateRoomEntered);
		_socket.on('connected', _onConnected);
		_socket.on('userjustkicked', _onUserKicked);
		_socket.on('userjustbanned', _onUserBanned);
		_socket.on('clear', _onClear);
		_socket.on('notice', _onNotice);
		_socket.on('userStartedTyping', _onUserStartedTyping);
		_socket.on('userStoppedTyping', _onUserStoppedTyping);
		_socket.on('userlist', _onUserlist);
		_socket.on('shutdown', function(){
			_socket.removeAllListeners();
			_socket.close();
			window.location.href = redirectUrl;
		});

		$(window).on('beforeunload',function() {
			ChatTypingBroadcasterFactory.releaseAll();
			_socket.close();
		});

			_initSubmit();
		};

		/**
		 * Sends enter room to server
		 *
		 * @param {number} roomId
		 */
		this.enterRoom = function (roomId) {
			logger.logServerRequest('enterRoom');
			_socket.emit('enterRoom', roomId);
		};

		/**
		 * @param {Function} callback
		 */
		this.onLoggedIn = function (callback) {
			_socket.on('loggedIn', function () {
				callback();
			});
		};

	this.userStartedTyping = function(roomId) {
		logger.logServerRequest('userStartedTyping');
		_socket.emit('userStartedTyping', roomId);
	}

	this.userStoppedTyping = function(roomId) {
		logger.logServerRequest('userStoppedTyping');
		_socket.emit('userStoppedTyping', roomId);
	}

	/**
	 * Displays chatmessage in chat
	 *
	 * @param {{
	 *	type:string,
	 *	timestamp: number,
	 *	content: string,
	 *	roomId: number,
	 *	from: {id: number, name: string},
	 *	format: {style: string, color: string, family: string, size: string}
	 * }} messageObject
	 *
	 * @private
	 */
	function _onMessage(messageObject) {
			gui.addMessage(messageObject);
		}

		/**
		 * Adds chat for user invitation
		 *
		 * @param {{
		 *	type:string,
		 *	timestamp: number,
		 *	content: string,
		 *	roomId: number,
		 *	title: string
		 *	owner: number
		 * }} messageObject
		 *
		 * @private
		 */
		function _onUserInvited(messageObject) {
			gui.addChatMessageArea(messageObject.title, messageObject.owner);
		}

		/**
		 * Enters a private Room
		 *
		 * @param {{
		 *	type:string,
		 *	timestamp: number,
		 *	content: string,
		 *	roomId: number,
		 *	title: string,
		 *	owner: number,
		 *	subscriber: {id: number, username: string},
		 *  usersInRoom: {Array}
		 * }} messageObject
		 *
		 * @private
		 */
		function _onPrivateRoomEntered(messageObject) {
			logger.logServerResponse('onPrivateRoomEntered');
		}

		function _onConnected(messageObject) {
			let loader = new ProfileImageLoader($.map(messageObject.users, function (val) {
				return val.id;
			}), function () {
				$(messageObject.users).each(function (i) {
					let data = {
						id: this.id,
						label: this.login,
						type: 'user',
						image: loader.getProfileImage(this.id)
					};
					$('#chat_users').ilChatUserList('add', data);

					userManager.add(data);

					$('#chat_messages').ilChatMessageArea('addMessage', {
						login: data.label,
						timestamp: messageObject.timestamp,
						type: 'connected'
					});
				});
			});
		}

		/**
		 * Kicks a user from chat
		 *
		 * @param {{
		 *	type:string,
		 *	timestamp: number,
		 *	content: string,
		 *	roomId: number,
		 * }} messageObject
		 *
		 * @private
		 */
		function _onUserKicked(messageObject) {
			logger.logServerResponse('onUserKicked');

			userManager.remove(user.id);

			// If user is kicked from sub room, redirect to main room

			$('#chat_users').ilChatUserList('removeById', user.id);
			window.location.href = redirectUrl + "&msg=kicked";
		}

		/**
		 * Banns a user from chat
		 *
		 * @param {{
		 *	type:string,
		 *	timestamp: number,
		 *	content: string,
		 *	roomId: number,
		 * }} messageObject
		 *
		 * @private
		 */
		function _onUserBanned(messageObject) {
			if (_socket) {
				_socket.removeAllListeners();
				_socket.close();
			}
			window.location.href = redirectUrl + "&msg=banned";
		}

		/**
		 * Clears chat history
		 *
		 * @param {{
		 *	type:string,
		 *	timestamp: number,
		 *	content: string,
		 *	roomId: number,
		 * }} messageObject
		 *
		 * @private
		 */
		function _onClear(messageObject) {
			$('#chat_messages').ilChatMessageArea('clearMessages');
		}

		/**
		 * Adds a notice to chat
		 *
		 * @param {{
		 *	type:string,
		 *	timestamp: number,
		 *	content: string,
		 *	roomId: number,
		 *	data: {}
		 * }} messageObject
		 *
		 * @private
		 */
		function _onNotice(messageObject) {
			messageObject.content = translation.translate(messageObject.content, messageObject.data);

			gui.addMessage(messageObject);
		}

		/**
		 * Updates the list of users.
		 *
		 * @param {{
		 *	type:string,
		 *	timestamp: number,
		 *	content: string,
		 *	roomId: number,
		 * 	users: {}
		 * }} messageObject
		 *
		 * @private
		 */
		function _onUserlist(messageObject) {
			let users = messageObject.users;

			logger.logServerResponse("onUserlist");

			userManager.clear();

			let loader = new ProfileImageLoader($.map(users, function (val) {
				return val.id;
			}), function () {
                            Object.values(users).forEach(function(otherUser){
                                const chatUser = {
				    id: otherUser.id,
				    label: otherUser.username,
				    type: 'user',
				    hide: otherUser.id == user.id,
				    image: loader.getProfileImage(otherUser.id)
				};

				userManager.add(chatUser);

				$('#chat_users').ilChatUserList('add', chatUser, chatUser.id, {hide: chatUser.hide});

				if (chatUser.id != user.id) {
				    $('.user_' + chatUser.id).show();
				} else {
				    $('.user_' + chatUser.id).hide();
				}
                            });

			    // remove old users
			    const  currentUsersInRoom = userManager.getUsersInRoom();

				for (let key in currentUsersInRoom) {
					const userId = currentUsersInRoom[key].id;
					if (!isIdInArray(userId, users)) {
						userManager.remove(userId);
					    $('#chat_users').ilChatUserList('removeById', userId);
					}
				}

			if ($('.online_user:visible').length == 0) {
				$('.no_users').show();
			} else {
				$('.no_users').hide();
			}
		});
	}
	
	function _onUserStartedTyping(message) {
		logger.logServerResponse("onUserStartedTyping");

		const subscriber = JSON.parse(message.subscriber),
			scope = message.roomId + '_0',
			generator = ChatTypingUsersTextGeneratorFactory.getInstance(scope);

		generator.addTypingSubscriber(subscriber.id, subscriber.username);

		gui.addTypingInfo(message, generator.text(
			il.Language
		));
	}

	function _onUserStoppedTyping(message) {
		logger.logServerResponse("onUserStoppedTyping");

		const subscriber = JSON.parse(message.subscriber),
			scope = message.roomId + '_0',
			generator = ChatTypingUsersTextGeneratorFactory.getInstance(scope);

		generator.removeTypingSubscriber(subscriber.id, subscriber.username);

		gui.addTypingInfo(message, generator.text(
			il.Language
		));
	}

	/**
	 * Setup message submit to server
	 *
	 * @private
	 */
	function _initSubmit() {
		$('#submit_message').click(function(e) {
			e.preventDefault();
			e.stopPropagation();
			_sendMessage();
		});

			// when the client hits ENTER on their keyboard
			$('#submit_message_text').keydown(function (e) {
				const keycode = e.keyCode || e.which;

				if (keycode === 13 && !e.shiftKey) {
					e.preventDefault();
					e.stopPropagation();

				$(this).blur();
				_sendMessage();
			}
		});

		$('#submit_message_text').keyup(function(e) {
			if (personalUserInfo.broadcast_typing !== true) {
				return;
			}

			const room_id = _scope;

			const broadcaster = ChatTypingBroadcasterFactory.getInstance(
				room_id + '_0',
				function() {
					serverConnector.userStartedTyping(room_id);
				},
				function() {
					serverConnector.userStoppedTyping(room_id);
				}
			);

			const keycode = e.keyCode || e.which;
			if (keycode === 13) {
				broadcaster.release();
				return;
			}

			broadcaster.registerTyping();
		});
	}

		/**
		 * Sent message to server
		 *
		 * @private
		 */
		function _sendMessage() {
			const $textInput = $('#submit_message_text'), 
				content = $textInput.val();

			if (content.trim() !== '') {
				const message = {
					content: content,
					format: {}
				};

				if (messageOptions['recipient'] != undefined && messageOptions['recipient'] != false) {
					message.target = {
						username: $('#chat_users').ilChatUserList('getDataById', messageOptions['recipient']).label,
						id: messageOptions['recipient'],
						public: messageOptions['public']
					}
				}

			$textInput.val('');

			if (personalUserInfo.broadcast_typing === true) {
				const room_id = _scope;

				const broadcaster = ChatTypingBroadcasterFactory.getInstance(
					room_id + '_0',
					function() {
						serverConnector.userStartedTyping(room_id);
					},
					function() {
						serverConnector.userStoppedTyping(room_id);
					}
				);

				broadcaster.release();
			}

			_socket.emit('message', message, scope);
			$textInput.focus();
		}
	}
};

	// Set recipient targeted/whispered
	function setRecipientOptions(recipient, isPublic) {
		messageOptions['recipient'] = recipient;
		messageOptions['public'] = isPublic;

		$('#message_recipient_info').children().remove();
		if (recipient) {
			messageOptions['recipient_name'] = $('#chat_users').ilChatUserList('getDataById', recipient).label;
			$('#message_recipient_info_all').hide();
			$('#message_recipient_info').html(
				$('<span>' + translation.translate(isPublic ? 'speak_to' : 'whisper_to', {
					user: $('#chat_users').ilChatUserList('getDataById', recipient).label,
					myname: personalUserInfo.name
				}) + '</span>')
					.append(
						$('<span> <a href="javascript:void(0);">(' + translation.translate('end_whisper') + ')</a></span>').click(
							function () {
								setRecipientOptions(false, 1);
							}
						)
					)
			).show();
		} else {
			messageOptions['recipient_name'] = null;
			$('#message_recipient_info_all').show();
			$('#message_recipient_info').hide();
		}
	}

	$.getAsObject = function (data) {
		if (typeof data == 'object') {
			return data;
		}
		try {
			return JSON.parse(data);
		} catch (e) {
			if (typeof console != 'undefined') {
				console.log(e);
				return {success: false};
			}
		}
	};

	$.fn.chat = function (baseurl, instance) {
		const gui = new GUI(translation);
		const userManager = new UserManager();
		iliasConnector = new ILIASConnector(posturl, new ILIASResponseHandler());
		const chatUsers = new ChatUsers('#chat_users', translation, iliasConnector);

		serverConnector = new ServerConnector(baseurl + '/' + instance, _scope, personalUserInfo, userManager, gui, initial.subdirectory);
		chatActions = new ChatActions('#chat_actions', translation, iliasConnector, userManager);

		//Setup Heartbeat to refresh the Session
		iliasConnector.heartbeatInterval(120 * 1000);
		serverConnector.init();
		serverConnector.onLoggedIn(function () {
			serverConnector.enterRoom(_scope, 0);

			if (initial.enter_room) {
				serverConnector.enterRoom(_scope, initial.enter_room);
			}

		});

		smileys.render();

		// Insert Chatheader into HTML next to AKTION-Button
		gui.renderHeaderAndActionButton();
		// When private rooms are disabled, dont show chat header
		// Resizes Chatwindow every 500 miliseconds
		gui.resizeChatWindowInInterval(500);
		// Initialize ChatMessageArea();
		gui.initChatMessageArea(initial.state);
		gui.addChatMessageArea(translation.translate('main'), 0);
		gui.showChatMessageArea(0);


		// Initialize Chatlist user actions
		chatUsers.init();
		// Initialize Chat Aktions Button
		chatActions.init();

		messageOptions = {
			'recipient': null,
			'recipient_name': null,
			'public': 1
		};

		// @TODO DONO;
		$('#enter_main').click(function (e) {
			e.preventDefault();
			e.stopPropagation();
			$('#chat_messages').ilChatMessageArea('show');
			$('#chat_users').find('.online_user').not('.hidden_entry').show();
		});

		//@TODO DONO
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
		});
		$('#tab_users').click();

		const loader = new ProfileImageLoader($.map(initial.users, function (val) {
			return val.id;
		}), function () {
			$(initial.users).each(function () {
				const tmp = {
					id: this.id,
					label: this.login,
					type: 'user',
					hide: this.id == personalUserInfo.id,
					image: loader.getProfileImage(this.id)
				};
				$('#chat_users').ilChatUserList('add', tmp, {hide: true});
				userManager.add(tmp);
			});
		});

		// Show initial messages
		$(initial.messages).each(function () {
			const message = this;

			message.timestamp = message.timestamp * 1000;

			if (message.type == 'notice') {
				if (message.content == 'connect' && message.data.id == personalUserInfo.id) {
					message.content = 'welcome_to_chat';
				}
				message.content = translation.translate(message.content, message.data);
			}
			$('#chat_messages').ilChatMessageArea('addMessage', message);
		});

		// Build more options
		function buildMoreOptions() {
			const res = [];
			for (let i in messageOptions) {
				if (messageOptions[i] == null || messageOptions[i] == false)
					continue;
				res.push(i + '=' + encodeURIComponent(messageOptions[i]));
			}
			return res.join('&');
		}

		// Handle incomming Message
		function handleMessage(message) {
			const messageObject = (typeof message == 'object') ? message : $.getAsObject(message);

			//@TODO Debug anders realisieren
			if (typeof DEBUG != 'undefined' && DEBUG) {
				$('#chat_messages').ilChatMessageArea('addMessage', {
					type: 'notice',
					message: messageObject.type
				});
			}
		}
	}

	let api = {
		run: function(appDomElementId, lang, baseurl, instance, scope, postUrl, initialConfig) {
			$("#submit_message_text").focus();

			$(document).click(function() {
				$(".dropdown-menu.menu").hide();
			});

			_scope = scope;
			room = scope;
			initial = initialConfig;
			initial.enter_room = initial.enter_room || 0;
			personalUserInfo = initial.userinfo;

			redirectUrl = initial.redirect_url;
			posturl = postUrl;

			logger = new Logger();
			translation = new Translation(lang);
			smileys = new Smileys(initial.smileys);
			
			$("#" + appDomElementId).chat(baseurl, instance);
		},
		leavePrivateRoom: function () {
			iliasConnector.leavePrivateRoom();
		},
		getSmileys: function () {
			return smileys;
		},
		getUserInfo: function() {
			return personalUserInfo;
		},
		formatISOTime: formatISOTime,
		formatISODate: formatISODate,
		translate: translate
	};

	return api;
}));
