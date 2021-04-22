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
	var format = translation.translate("timeformat");
	var date = new Date(time);

	format = format.replace(/H/, formatToTwoDigits(date.getHours()));
	format = format.replace(/i/, formatToTwoDigits(date.getMinutes()));
	format = format.replace(/s/, formatToTwoDigits(date.getSeconds()));

	return format;
}

function formatISODate(time) {
	var format = translation.translate("dateformat");
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

/** @TODO Remove Dirty vars **/
var currentRoom;

var subRoomId;

var redirectUrl;
/**
 * @type {Translation}
 */
var translation, posturl;
/**
 * @type {ServerConnector}
 */
var serverConnector;

/**
 * @type {Logger}
 */
var logger;

var personalUserInfo;

var _scope;


var Logger = function Logger()
{

	this.logServerResponse = function(message) {
		_log('Server-Response', message);
	};

	this.logServerRequest = function(message) {
		_log('Server-Request', message);
	};

	this.logILIASResponse = function(message) {
		_log('ILIAS-Response', message);
	};

	this.logILIASRequest = function(message) {
		_log('ILIAS-Request', message);
	};

	function _log(type, message) {
		//console.log(type, message);
	}
};

/**
 * This class translates all translation key to language text.
 * It is only able to translate thouse keys, which are delivered into _lang via the constructor
 *
 * @param {array} _lang
 * @constructor
 */
var Translation = function Translation(_lang) {

	/**
	 * Translates a string
	 *
	 * @param {string} key
	 * @param {{}} [arguments]
	 * @returns {string}
	 */
	this.translate = function (key, arguments) {
		if (_lang[key]) {
			var lng = _lang[key];
			if (arguments != undefined) {
				for (var i in arguments) {
					lng = lng.replace(new RegExp('#' + i + '#', 'g'), arguments[i]);
				}
			}
			return lng;
		}
		return '#' + key + '#';
	}
};

var ProfileImageLoader = function(_userId, _onFinishCallback) {
	var requestUserImages = function(userIds) {
		var difference = [];

		$.grep(userIds, function(el) {
			if ($.inArray(el, Object.keys(ProfileImageLoader._imagesByUserId)) == -1) difference.push(el);
		});

		if (difference.length > 0) {
			$.get(
				initial.profile_image_url + '&usr_ids=' + userIds.join(','),
				function (response) {
					$.each(response, function (id, item) {
						var img = new Image();
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

	this.getProfileImage = function(_userId) {
		if (ProfileImageLoader._imagesByUserId.hasOwnProperty(_userId)) {
			return ProfileImageLoader._imagesByUserId[_userId];
		}

		var img = new Image();
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
var Smileys = function Smileys(_smileys) {

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

		for (var i in _smileys) {
			while (message.indexOf(i) != -1) {
				message = message.replace(i, '<img src="' + _smileys[i] + '" />');
			}
		}

		return message;
	};

	this.render = function() {
		if (typeof _smileys == "object") {
			if (_smileys.length == 0) {
				return;
			}
			// Emoticons
			var $emoticons_flyout_trigger = $('<a></a>');
			var $emoticons_flyout = $('<div id="iosChatEmoticonsPanelFlyout"></div>');
			var $emoticons_panel = $('<div id="iosChatEmoticonsPanel"></div>')
					.append($emoticons_flyout_trigger)
					.append($emoticons_flyout);

			$("#submit_message_text").css("paddingLeft", "25px").after($emoticons_panel);

			$emoticons_panel.css("top", "3px");

			var $emoticons_table = $("<table></table>");
			var $emoticons_row = null;
			var cnt = 0;
			var emoticonMap = new Object();
			for (var i in _smileys) {
				if (emoticonMap[_smileys[i]]) {
					var $emoticon = emoticonMap[_smileys[i]];
				} else {
					if (cnt % 6 == 0) {
						$emoticons_row = $("<tr></tr>");
						$emoticons_table.append($emoticons_row);
					}

					var $emoticon = $('<img src="' + _smileys[i] + '" alt="" title="" />');
					$emoticon.data("emoticon", i);
					$emoticons_row.append($('<td></td>').append($('<a></a>').append($emoticon)));

					emoticonMap[_smileys[i]] = $emoticon;

					++cnt;
				}
				$emoticon.attr({
					alt:   [$emoticon.attr('alt').toString(), i].join(' '),
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
var GUI = function GUI(_translation) {

	var _prevSize = {width: 0, height: 0};
	var _$anchor = $('#chat_messages');

	this.renderHeaderAndActionButton = function() {
		$('<div id="tttt" style="white-space:nowrap;"></div>')
				.append($('#chat_actions_wrapper'))
				.insertBefore($('.il_HeaderInner').find('h1'));
	};

	/**
	 * @param {boolean} privateRoomsEnabled
	 */
	this.showHeadline = function(privateRoomsEnabled){
		if (!privateRoomsEnabled) {
			$('#chat_head_line').hide();
		}
	};

	/**
	 * @param {number} interval
	 */
	this.resizeChatWindowInInterval = function(interval) {
		window.setInterval(function () {
			var body = $('body');
			var currentSize = {width: body.width(), height: body.height()};
			if (currentSize.width != _prevSize.width || currentSize.height != _prevSize.height) {
				$('#chat_sidebar_wrapper').height($('#chat_sidebar').parent().height() - $('#chat_sidebar_tabs').height());
				_prevSize = {width: body.width(), height: body.height()};
			}
		}, interval);
	}

	this.initChatMessageArea = function() {
		_$anchor.ilChatMessageArea();
	};

	this.addChatMessageArea = function(subRoomId, title, owner) {
		_$anchor.ilChatMessageArea('addScope', subRoomId, {
			title: title,
			id:    subRoomId,
			owner: owner
		});
	};

	this.showChatMessageArea = function(scope) {
		_$anchor.ilChatMessageArea('show', scope);
	};

	this.addMessage = function(subRoomId, messageObject) {
		_$anchor.ilChatMessageArea('addMessage', subRoomId, messageObject);
	};

};

/**
 * This class manages all users for all rooms.
 *
 * @constructor
 */
var UserManager = function UserManager() {

	/**
	 * Stores all users by each room.
	 *
	 * @type {{}}
	 * @private
	 */
	var _usersByRoom = {};

	/**
	 * Adds a user to the delivered room.
	 *
	 * @param {JSON} userdata
	 * @param {string} roomId
	 * @returns {boolean}
	 */
	this.add = function(userdata, roomId) {
		if (!_usersByRoom['room_' + roomId]) {
			_usersByRoom['room_' + roomId] = [];
		}
		for (var i in _usersByRoom['room_' + roomId]) {
			var current = _usersByRoom['room_' + roomId][i];
			if (current.id == userdata.id) {
				_usersByRoom['room_' + roomId][i].label = userdata.label;
				return false;
			}
		}
		_usersByRoom['room_' + roomId].push(userdata);
		return true;
	};

	/**
	 * Remove a delivered user from the delivered room.
	 *
	 * @param {number} userId
	 * @param {string} roomId
	 */
	this.remove = function(userId, roomId) {
		if (_usersByRoom['room_' + roomId]) {
			for (var i in _usersByRoom['room_' + roomId]) {
				var user = _usersByRoom['room_' + roomId][i];
				if(user.id == userId) {
					delete _usersByRoom['room_' + roomId][i];
				}
			}
		}
	};

	/**
	 * Get all users in a delivered room.
	 *
	 * @param {string} roomId
	 * @returns {Array}
	 */
	this.getUsersInRoom = function(roomId) {
		return _usersByRoom['room_' + roomId] || [];
	};

	/**
	 * Removes all users from the delivered room.
	 *
	 * @param {string} roomId
	 */
	this.clear = function(roomId) {
		_usersByRoom['room_' + roomId] = [];
	};
};

/**
 * @TODO Check where this class is used. All actions aren't implemented yer. Maybe this class can be removed
 *
 * PrivateRooms
 *
 * @param {string} selector
 * @param {Translation} _translation
 * @param {ILIASConnector} _connector
 * @constructor
 */
var PrivateRooms = function PrivateRooms(selector, _translation, _connector) {

	var _$anchor = $(selector);

	/**
	 * Initialize ilChatList with private room actions for jQuery-Object selected by
	 * the selector
	 */
	this.init = function() {
		_$anchor.ilChatList([
			_addEnterAction(),
			_addLeaveAction(),
			_addDeleteAction()
		]);
	};

	this.addRoom = function(id, label, owner) {
		_$anchor.ilChatList('add', {
			id:    id,
			label: label,
			owner: owner,
			type: 'room'
		});
	};

	/**
	 * @returns {{label: string, callback: callback}}
	 * @private
	 */
	function _addEnterAction() {
		return {
			label: _translation.translate('enter'),
			callback: function() {
				alert('PrivateRooms._addEnterAction');
				//@TODO ILIASConnector.enterPrivateRoom
				//_connector.enterPrivateRoom(this.id);
			}
		};
	}

	/**
	 * @returns {{label: string, callback: callback}}
	 * @private
	 */
	function _addLeaveAction() {
		return {
			label: _translation.translate('leave'),
			callback: function() {
				alert('PrivateRooms._addLeaveAction');
				//@TODO ILIASConnector.leavePrivateRoom
				/*_connector.leavePrivateRoom(this.id);*/
			}
		};
	}

	/**
	 * @returns {{label: string, callback: callback, permission: string[]}}
	 * @private
	 */
	function _addDeleteAction() {
		return {
			label: _translation.translate('delete_private_room'),
			callback: function() {
				alert('PrivateRooms._addDeleteAction');
				//@TODO ILIASConnector.deletePrivateRoom
				//_connector.deletePrivateRoom(this.id)
			},
			permission: ['moderator', 'owner']
		};
	}
};

/**
 * This class renders all available action for a user in a chat room.
 *
 * @param {string} selector
 * @param {Translation} _translator
 * @param {ILIASConnector} _connector
 * @constructor
 */
var ChatUsers = function ChatUsers(selector, _translator, _connector) {

	var _$anchor = $(selector);

	/**
	 * Initializes all available action.
	 */
	this.init = function() {
		var actions = [];

		actions.push(_addAddressAction());
		actions.push(_addWhisperAction());

		if(personalUserInfo.moderator == true || (subRoomId > 0 && (room = $('#private_rooms').ilChatList('getDataById', subRoomId)).owner == personalUserInfo.id )) {
			actions.push(_addKickAction());
		}
		if(personalUserInfo.moderator == true || (subRoomId > 0 && (room = $('#private_rooms').ilChatList('getDataById', subRoomId)).owner == personalUserInfo.id )) {
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
			callback: function() {
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
			callback: function() {
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
			callback: function() {
				if(confirm(_translator.translate('kick_question'))) {
					_connector.kick(this.id, subRoomId);
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
			callback: function() {
				if(confirm(_translator.translate('ban_question'))) {
					_connector.ban(this.id, subRoomId);
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
 * @param {number} _privateRoomsEnabled
 * @oaram {UserManager} userManager
 */
var ChatActions = function ChatActions(selector, _translation, _connector, _privateRoomsEnabled, userManager) {

	var _$anchor = $(selector);
	var _menuEntries;


	// Done
	/**
	 * Setup ChatActions
	 */
	this.init = function() {

		_$anchor.click(function (e) {
			e.preventDefault();
			e.stopPropagation();
			_resetEntries();

			$(this).removeClass('chat_new_events');

			_addLeaveSubRoomAction(); // Done, just permission checks
			_addDeleteSubRoomAction(); // Done, just permission checks
			_addCreateSubRoomAction(); // Done, just permission checks
			_addInviteSubRoomAction();
			_addClearHistoryAction();
			_addSeparator(); // Done
			_addEnterRoomActions(); // Done, just permission checks

			$(this).ilChatMenu('show', _menuEntries, true);
		});
	};

	/**
	 * Clears all menu entries
	 *
	 * @private
	 */
	function _resetEntries(){
		_menuEntries = [];
	}

	/**
	 * Adds a separator to the chat action menu
	 *
	 * @private
	 */
	function _addSeparator() {
		if(_privateRoomsEnabled) {
			_menuEntries.push({separator: true});
		}
	}

	/**
	 * Adds a leave sub room action.
	 *
	 * @TODO Check if user is in a subroom
	 *
	 * @private
	 */
	function _addLeaveSubRoomAction() {
		if(subRoomId) {
			_menuEntries.push({
				label: _translation.translate('leave'),
				callback: function() {
					_connector.leavePrivateRoom(subRoomId);
				}
			});
		}
	}

	/**
	 * Adds a delete room action.
	 *
	 * @TODO Check if user is in a subroom and has permission to delete a subroom
	 *
	 * @private
	 */
	function _addDeleteSubRoomAction() {
		if(subRoomId > 0 && ((room = $('#private_rooms').ilChatList('getDataById', subRoomId)).owner == personalUserInfo.id || personalUserInfo.moderator == true)) {
			_menuEntries.push({
				label: _translation.translate('delete_private_room'),
				callback: function() {
					if(confirm(_translation.translate('delete_private_room_question'))) {
						_connector.deletePrivateRoom(subRoomId);
					}
				},
				permission: ['moderator', 'owner']
			});
		}
	}

	/**
	 * Adds a create sub room action. On click it opens an input dialog to enter the sub room name.
	 *
	 * @TODO Check if privatRooms are enabled.
	 *
	 * @private
	 */
	function _addCreateSubRoomAction() {
		if(_privateRoomsEnabled) {
			_menuEntries.push({
				label: _translation.translate('create_private_room'),
				callback: function() {
					$('#create_private_room_dialog').ilChatDialog({
						title:			_translation.translate('create_private_room'),
						positiveAction: function () {
							var name = $('#new_room_name').val();
							if (name.trim() == '') {
								alert(_translation.translate('empty_name'));
								return false;
							}

							_connector.createPrivateRoom(name);
						}
					});
				}
			});
		}
	}

	/**
	 * Adds for each private room an enter private room action.
	 *
	 * @TODO Check if privateRooms are enabled
	 *
	 * @private
	 */
	function _addEnterRoomActions() {
		if(_privateRoomsEnabled) {
			var rooms = $('#private_rooms').ilChatList('getAll');

			rooms.sort(function(a, b) {
				if(a.id == 0) {
					return -1;
				} else if(b.id == 0) {
					return 1;
				}
				return a.label < b.label ? -1 : 1;
			});

			$.each(rooms, function() {

				var room = this;
				var classes = ['room_' + room.id];

				if(currentRoom == room.id) {
					classes.push('in_room');
				}
				if(room.new_events) {
					classes.push('chat_new_events');
				}

				_menuEntries.push({
					label: this.label,
					icon: 'templates/default/images/' + (!room.id ? 'icon_chtr.svg' : 'icon_chtr.svg'),
					addClass: classes.join(' '),
					callback: function() {
						room.new_events = false;
						if (currentRoom == room.id) {
							return;
						} else if (room.id == 0 && currentRoom != 0) {
							_connector.leavePrivateRoom(currentRoom);
							currentRoom = 0;
							return;
						}
						_connector.enterPrivateRoom(room.id);
					}
				});
			});
		}
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
		if(!subRoomId || (subRoomId && ((room = $('#private_rooms').ilChatList('getDataById', subRoomId)).owner == personalUserInfo.id) || personalUserInfo.moderator == true)) {
			_menuEntries.push(
					{
						label:    _translation.translate('invite_users'),
						callback: function () {
							$('#invite_users_container').ilChatDialog({
								title: _translation.translate('invite_users'),
								close: function () {
									$('#invite_user_text_wrapper').val('');
								},
								positiveAction:   function () {
								},
								disabled_buttons: ['ok']
							});

							$('#invite_users_global').click(function(){
								$('#invite_users_available').children().remove();
								$('#invite_user_text_wrapper').show();

								$('#invite_user_text').keyup(function(){
									if($(this).val().length > 2) {
										$.get(
												posturl.replace(/postMessage/, 'inviteUsersToPrivateRoom-getUserList') + '&q=' + $('#invite_user_text').val(),
												function(response){
													$('#invite_users_available').children().remove();
													$(response.items).each(function() {
														var usersInRoom = userManager.getUsersInRoom(currentRoom);
														if(!isIdInArray(this.id, usersInRoom)) {
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

							$('#invite_users_in_room').click(function(){
								var availableUsers = $('#invite_users_available');
								$(availableUsers).children().remove();
								$('#invite_user_text_wrapper').hide();

								var chatUsers = userManager.getUsersInRoom(0); // load all User connected to main room.
								var usersInRoom = userManager.getUsersInRoom(currentRoom);

								$(chatUsers).each(function(){
									if(!isIdInArray(this.id, usersInRoom)) {
										_addUserForInvitation(this.id, 'byId', this.label)
									}
								});

								if($(availableUsers).children().length == 0) {
									$('#invite_users_in_room').remove();
									$('#radioText').remove();
									$('#invite_users_global').prop('checked', 'checked').click();
								}
							}).click();
						}
					}
			);
		}
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
		if(personalUserInfo.moderator) {
			_menuEntries.push({
				label: _translation.translate('clear_room_history'),
				callback: function() {
					if (confirm(_translation.translate('clear_room_history_question'))) {
						_connector.clear(currentRoom);
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
		var link = $('<a></a>')
				.prop('href', '#')
				.text(label)
				.click(function (e) {
					e.preventDefault();
					e.stopPropagation();
					_connector.inviteToPrivateRoom(subRoomId, userValue, invitationType);
				});
		var line =  $('<li></li>')
				.addClass('invite_user_line_id')
				.addClass('invite_user_line')
				.append(link);

		$('#invite_users_available').append(line);
	}
};

/**
 * This class handles responses of all asynchronous request done by ILIASConnector.
 * It has to be passed to the ILIASConnector instance.
 *
 * @constructor
 */
var ILIASResponseHandler = function ILIASResponseHandler() {

	/**
	 * Handles the response of a createPrivateRoom request.
	 * It adds the created room to the private_rooms list and initiates a enterPrivateRoom request to switch to the
	 * recently created room.
	 *
	 * @param {{subRoomId: number, title: string, owner: number}} response
	 *
	 * @TODO Possible unused
	 */
	this.createPrivateRoom = function(response) {
		logger.logILIASResponse('createPrivateRoom');
		return !_validate(response);
	};

	/**
	 * Handles the response of a enterPrivateRoom request.
	 * It changes the currentRoom and shows the related ilChatMessageArea
	 *
	 * @param {{}} response
	 */
	this.enterPrivateRoom = function(response) {
		logger.logILIASResponse('enterPrivateRoom');
		if(!_validate(response))
		{
			return
		}

		currentRoom = response.subRoomId;
		$('#chat_messages').ilChatMessageArea('show', currentRoom);
		serverConnector.enterRoom(_scope, currentRoom); //@TODO Remove the static roomNumber, maybe extract this to another position.
	};

	/**
	 * Handles the response of a leavePrivateRoom request.
	 * It changes the currentRoom to main room and shows the related ilChatMessageArea
	 *
	 * @param {{}} response
	 */
	this.leavePrivateRoom = function(response) {
		if (!_validate(response)) {
			return;
		}

		currentRoom = 0; //@todo Duplicated currentRoom and subRoomId
		subRoomId = 0;	// @TODO Duplicated subRoomId and currentRoom
		$('#chat_messages').ilChatMessageArea('show', 0);
	};

	/**
	 * Handles the response of an inviteToPrivateRoom request.
	 * It closes the invitation dialog.
	 *
	 * @param {{}} response
	 */
	this.inviteToPrivateRoom = function(response) {
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
	this.default = function(response) {
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
		if(!response.success) {
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
var ILIASConnector = function ILIASConnector(_postUrl, responseHandler) {

	var _self = this;

	/**
	 * Sends a heartbeat to ILIAS in a delivered interval. It is used to keep the session for an ILIAS user open.
	 *
	 * @param {number} interval
	 */
	this.heartbeatInterval = function(interval) {
		window.setInterval(function () {
			_sendRequest('poll');
		}, interval);
	};

	/**
	 * Sends a request to ILIAS to create a new private room.
	 *
	 * @param {string} name
	 */
	this.createPrivateRoom = function(name) {
		logger.logILIASRequest('createPrivateRoom');
		_sendRequest('privateRoom-create', {title: name}, function(response) {
			var valid = responseHandler.default(response);
			if(valid) {
				_self.enterPrivateRoom(response.subRoomId);
			}
		});
	};

	/**
	 * Sends a request to ILIAS to enter a private room.
	 *
	 * @param {string} subRoomId
	 */
	this.enterPrivateRoom = function(subRoomId) {
		logger.logILIASRequest('enterPrivateRoom');
		_sendRequest('privateRoom-enter', {sub: subRoomId}, responseHandler.enterPrivateRoom);
	};

	/**
	 * Sends a request to ILIAS to leave a private room.
	 *
	 * @param {string} roomId
	 */
	this.leavePrivateRoom = function(roomId) {
		logger.logILIASRequest('leavePrivateRoom');
		_sendRequest('privateRoom-leave', {sub: roomId}, responseHandler.leavePrivateRoom);
	};

	/**
	 * Sends a request to ILIAS to delete a private room.
	 *
	 * @param {string} roomId
	 */
	this.deletePrivateRoom = function(roomId) {
		_sendRequest('privateRoom-delete', {sub: roomId}, responseHandler.default)
	};

	/**
	 * Sends a request to ILIAS to invite a specific user to a private room.
	 * The invitation can be done by two types
	 * 	1. byId
	 * 	2. byLogin
	 *
	 * @param {number} subRoomId
	 * @param {string} userValue
	 * @param {string} invitationType
	 */
	this.inviteToPrivateRoom = function(subRoomId, userValue, invitationType) {
		_sendRequest('inviteUsersToPrivateRoom-' + invitationType, {sub:subRoomId, user: userValue}, responseHandler.inviteToPrivateRoom);
	};

	/**
	 * Sends a request to ILIAS to clear the chat history
	 *
	 * @param subRoomId
	 */
	this.clear = function(subRoomId) {
		_sendRequest('clear', {sub: subRoomId}, responseHandler.default);
	};

	/**
	 * Sends a request to ILIAS to kick a user from a specific room. The room can either be a private or the main room.
	 *
	 * @param {number} userId
	 * @param {number} subRoomId
	 */
	this.kick = function(userId, subRoomId) {
		_sendRequest('kick', {user: userId, sub: subRoomId}, responseHandler.default);
	};

	/**
	 * Sends a request to ILIAS to ban a user from a specific room. The room can either be a private or the main room.
	 *
	 * @param {number} userId
	 */
	this.ban = function(userId) {
		_sendRequest('ban-active', {user: userId, sub: subRoomId}, responseHandler.default);
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
		$.get(_postUrl.replace(/postMessage/, action) + _generateParamsString(params), function(response) {
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
		var string = '';
		for(var key in params) {
			string += '&' + key + '=' + encodeURIComponent(params[key]);
		}
		return string;
	}
};

/**
 * This class connects the client to the related chat server. Communication is handled through websockets as far as
 * it is supported by the users browser. Otherwise it uses polling method to communicate. Messages are send through
 * `socket.emit`. Messages are received through `socket.on`. There can be 3 types of messages.
 *	1. Text messages which are send by the chat users
 *	2. Notification messages. This are informational messages which are triggered by the system.
 *	3. Action messages. This messages triggers action which have to be executed in the client. This messages are
 *		triggered by the System.
 *
 * @param url
 * @param scope
 * @param user
 * @param {UserManager} userManager
 * @param {GUI} gui
 * @constructor
 */
var ServerConnector = function ServerConnector(url, scope, user, userManager, gui, subdirectory) {

	var _socket;

	/**
	 * Setup server connector
	 */
	this.init = function() {
		_socket = io.connect(url, {path: subdirectory});

		_socket.on('message', _onMessage);
		_socket.on('connect', function(){
			_socket.emit('login', user.login, user.id);
		});
		_socket.on('user_invited', _onUserInvited);
		_socket.on('private_room_entered', _onPrivateRoomEntered);
		_socket.on('private_room_left', _onPrivateRoomLeft);
		_socket.on('private_room_created', _onPrivateRoomCreated);
		_socket.on('private_room_deleted', _onPrivateRoomDeleted);
		_socket.on('connected', _onConnected);
		_socket.on('userjustkicked', _onUserKicked);
		_socket.on('userjustbanned', _onUserBanned);
		_socket.on('clear', _onClear);
		_socket.on('notice', _onNotice);
		_socket.on('userlist', _onUserlist);
		_socket.on('shutdown', function(){
			_socket.removeAllListeners();
			_socket.close();
			window.location.href = redirectUrl;
		});

		$(window).on('beforeunload',function() {
			_socket.close();
		});

		_initSubmit();
	};

	/**
	 * Sends enter room to server
	 *
	 * @param {number} roomId
	 * @param {number} subRoomId
	 */
	this.enterRoom = function(roomId, subRoomId) {
		logger.logServerRequest('enterRoom');
		_socket.emit('enterRoom', roomId, subRoomId);
	};

	/**
	 * @param {Function} callback
	 */
	this.onLoggedIn = function(callback) {
		_socket.on('loggedIn', function(){
			callback();
		});
	};

	/**
	 * Displays chatmessage in chat
	 *
	 * @param {{
	 *	type:string,
	 *	timestamp: number,
	 *	content: string,
	 *	roomId: number,
	 *	subRoomId: number,
	 *	from: {id: number, name: string},
	 *	format: {style: string, color: string, family: string, size: string}
	 * }} messageObject
	 *
	 * @private
	 */
	function _onMessage(messageObject) {

		$('#private_rooms').ilChatList('setNewEvents', messageObject.subRoomId, subRoomId != messageObject.subRoomId);

		gui.addMessage(messageObject.subRoomId, messageObject);
	}

	/**
	 * Adds chat for user invitation
	 *
	 * @param {{
	 *	type:string,
	 *	timestamp: number,
	 *	content: string,
	 *	roomId: number,
	 *	subRoomId: number,
	 *	title: string
	 *	owner: number
	 * }} messageObject
	 *
	 * @private
	 */
	function _onUserInvited(messageObject){
		gui.addChatMessageArea(messageObject.subRoomId, messageObject.title, messageObject.owner);

		$('#private_rooms').ilChatList('add', {
			id:    messageObject.subRoomId,
			label: messageObject.title,
			type:  'room',
			owner: messageObject.owner
		});
	}

	/**
	 * Enters a private Room
	 *
	 * @param {{
	 *	type:string,
	 *	timestamp: number,
	 *	content: string,
	 *	roomId: number,
	 *	subRoomId: number,
	 *	title: string,
	 *	owner: number,
	 *	subscriber: {id: number, username: string},
	 *  usersInRoom: {Array}
	 * }} messageObject
	 *
	 * @private
	 */
	function _onPrivateRoomEntered(messageObject){
		logger.logServerResponse('onPrivateRoomEntered');

		if (messageObject.subscriber.id == user.id && currentRoom != messageObject.subRoomId) {
			currentRoom = messageObject.subRoomId;
			gui.showChatMessageArea(currentRoom);
		}
	}

	/**
	 * @private
	 */
	function _onPrivateRoomLeft(messageObject){
		if (messageObject.sub && messageObject.sub == subRoomId) {
			$('#chat_users').find('.user_' + messageObject.user).hide();
		}
		userManager.remove(messageObject.user, messageObject.sub);
		if ($('.online_user:visible').length == 0) {
			$('.no_users').show();
		}
		else {
			$('.no_users').hide();
		}
	}


	/**
	 * Creates a private room for chat
	 *
	 *@param {{
	 *	type:string,
	 *	timestamp: number,
	 *	content: string,
	 *	roomId: number,
	 *	subRoomId: number,
	 *	title: string
	 *	ownerId: number
	 *	title: string
	 * }} messageObject
	 * @private
	 */
	function _onPrivateRoomCreated(messageObject){
		logger.logServerResponse('private_room_created');

		$('#chat_messages').ilChatMessageArea('addScope', messageObject.subRoomId, messageObject);
		$('#private_rooms').ilChatList('add', {
			id:    messageObject.subRoomId,
			label: messageObject.title,
			type:  'room',
			owner: messageObject.ownerId
		});
	}

	/**
	 * Deltes a private room from chat
	 *
	 * @param {{
	 *	type:string,
	 *	timestamp: number,
	 *	content: string,
	 *	roomId: number,
	 *	subRoomId: number,
	 *	title: string
	 *	owner: number
	 * }} messageObject
	 *
	 * @private
	 */
	function _onPrivateRoomDeleted(messageObject){
		$('#private_rooms').ilChatList('removeById', messageObject.subRoomId);
		$('#chat_actions').find('span.room_'+messageObject.subRoomId).closest('li').remove();

		if (messageObject.subRoomId == currentRoom) {
			currentRoom = 0;
			gui.showChatMessageArea(currentRoom);
		}
	}

	function _onConnected(messageObject){
		var loader = new ProfileImageLoader($.map(messageObject.users, function(val) {
			return val.id;
		}), function() {
			$(messageObject.users).each(function (i) {
				var data = {
					id:    this.id,
					label: this.login,
					type:  'user',
					image: loader.getProfileImage(this.id)
				};
				$('#chat_users').ilChatUserList('add', data);

				userManager.add(data, 0);
				if (subRoomId) {
					$('.user_' + this.id).hide();
				}

				$('#chat_messages').ilChatMessageArea('addMessage', 0, {
					login:     data.label,
					timestamp: messageObject.timestamp,
					type:      'connected'
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
	 *	subRoomId: number,
	 * }} messageObject
	 *
	 * @private
	 */
	function _onUserKicked(messageObject){
		logger.logServerResponse('onUserKicked');

		userManager.remove(user.id, messageObject.subRoomId);

		// If user is kicked from sub room, redirect to main room
		if (messageObject.subRoomId > 0) {
			currentRoom = 0;
			gui.showChatMessageArea(0);
		} else {
			$('#chat_users').ilChatUserList('removeById', user.id);
			window.location.href = redirectUrl + "&msg=kicked";
		}
	}
	
	/**
	 * Banns a user from chat
	 *
	 * @param {{
	 *	type:string,
	 *	timestamp: number,
	 *	content: string,
	 *	roomId: number,
	 *	subRoomId: number,
	 * }} messageObject
	 *
	 * @private
	 */
	function _onUserBanned(messageObject){
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
	 *	subRoomId: number,
	 * }} messageObject
	 *
	 * @private
	 */
	function _onClear(messageObject){
		$('#chat_messages').ilChatMessageArea('clearMessages', messageObject.subRoomId);
	}

	/**
	 * Adds a notice to chat
	 *
	 * @param {{
	 *	type:string,
	 *	timestamp: number,
	 *	content: string,
	 *	roomId: number,
	 *	subRoomId: number,
	 *	data: {}
	 * }} messageObject
	 *
	 * @private
	 */
	function _onNotice(messageObject){
		messageObject.content = translation.translate(messageObject.content, messageObject.data);
		if(messageObject.subRoomId == -1) {
			messageObject.subRoomId = subRoomId;
		}

		gui.addMessage(messageObject.subRoomId, messageObject);
	}

	/**
	 * Updates the list of users for the delivered subRoomId
	 *
	 * @param {{
	 *	type:string,
	 *	timestamp: number,
	 *	content: string,
	 *	roomId: number,
	 *	subRoomId: number,
	 * 	users: {}
	 * }} messageObject
	 *
	 * @private
	 */
	function _onUserlist(messageObject) {
		logger.logServerResponse("onUserlist");
		var users = messageObject.users;

		userManager.clear(messageObject.subRoomId);

		if(messageObject.subRoomId == currentRoom) {
			$('#chat_users').ilChatUserList('clear');
		}

		var loader = new ProfileImageLoader($.map(users, function(val) {
			return val.id;
		}), function() {
			for(var key in users) {
				if(users.hasOwnProperty(key)) {
					var chatUser = {
						id: users[key].id,
						label: users[key].username,
						type: 'user',
						hide: users[key].id == user.id,
						image: loader.getProfileImage(users[key].id)
					};

					userManager.add(chatUser, messageObject.subRoomId);

					if(messageObject.subRoomId == currentRoom) {
						$('#chat_users').ilChatUserList('add', chatUser, chatUser.id, {hide: chatUser.hide});
					}

					if (chatUser.id != user.id) {
						$('.user_' + chatUser.id).show();
					} else {
						$('.user_' + chatUser.id).hide();
					}
				}
			}

			// remove old users
			var currentUsersInRoom = userManager.getUsersInRoom(messageObject.subRoomId);
			for(var key in currentUsersInRoom) {
				var userId = currentUsersInRoom[key].id;
				if(!isIdInArray(userId, users)) {
					userManager.remove(userId, messageObject.subRoomId);
					if (messageObject.subRoomId == currentRoom) {
						$('#chat_users').ilChatUserList('removeById', userId);
					}
				}
			}

			if ($('.online_user:visible').length == 0) {
				$('.no_users').show();
			} else {
				$('.no_users').hide();
			}
		});
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
		$('#submit_message_text').keydown(function(e) {
			var keycode = e.keyCode || e.which;
			if(keycode === 13 && !e.shiftKey) {
				e.preventDefault();
				e.stopPropagation();

				$(this).blur();
				_sendMessage();
			}
		});
	}

	/**
	 * Sent message to server
	 *
	 * @private
	 */
	function _sendMessage() {
		var $textInput = $('#submit_message_text');
		var content = $textInput.val();
		if(content.trim() != '')
		{
			var message = {
				content: content,
				format: {}
			};

			if(messageOptions['recipient'] != undefined && messageOptions['recipient'] != false) {
				message.target = {
					username: $('#chat_users').ilChatUserList('getDataById', messageOptions['recipient']).label,
					id: messageOptions['recipient'],
					public:  messageOptions['public']
				}
			}

			$textInput.val('');
			_socket.emit('message', message, scope, currentRoom);
			$textInput.focus();
		}
	}
};

function translate(key, arguments){
	return translation.translate(key, arguments);
}

var messageOptions = [];

var smileys;
var iliasConnector;
var chatActions;

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
							user:   $('#chat_users').ilChatUserList('getDataById', recipient).label,
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
	}
	else {
		messageOptions['recipient_name'] = null;
		$('#message_recipient_info_all').show();
		$('#message_recipient_info').hide();
	}
}

il.Util.addOnLoad(function () {
	$("#submit_message_text").focus();

	function closeMenus() {
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
		};

		$.fn.chat = function (lang, baseurl, session_id, instance, scope, postUrl, initial) {
			currentRoom = 0;
			_scope = scope;
			var user = initial.userinfo;
			initial.enter_room = initial.enter_room || 0;

			// catch current user information from initial data
			personalUserInfo = user;
			redirectUrl = initial.redirect_url;

			posturl = postUrl;
			logger = new Logger();
			translation		= new Translation(lang);
			var gui				= new GUI(translation);
			var userManager		= new UserManager();
			smileys			= new Smileys(initial.smileys);
			iliasConnector	= new ILIASConnector(posturl, new ILIASResponseHandler());
			serverConnector 	= new ServerConnector(baseurl + '/'+instance, scope, user, userManager, gui, initial.subdirectory);
			var privateRooms	= new PrivateRooms('#private_rooms', translation, iliasConnector);
			var chatUsers		= new ChatUsers('#chat_users', translation, iliasConnector);
			chatActions		= new ChatActions('#chat_actions', translation, iliasConnector, initial.private_rooms_enabled, userManager);

			//Setup Heartbeat to refresh the Session
			iliasConnector.heartbeatInterval(120 * 1000);
			serverConnector.init();
			serverConnector.onLoggedIn(function(){
				serverConnector.enterRoom(scope, 0);

				if(initial.enter_room)
				{
					serverConnector.enterRoom(scope, initial.enter_room);
				}

			});

			smileys.render();

			// Insert Chatheader into HTML next to AKTION-Button
			gui.renderHeaderAndActionButton();
			// When private rooms are disabled, dont show chat header
			gui.showHeadline(initial.private_rooms_enabled);
			// Resizes Chatwindow every 500 miliseconds
			gui.resizeChatWindowInInterval(500);
			// Initialize ChatMessageArea();
			gui.initChatMessageArea();
			gui.addChatMessageArea(0, translation.translate('main'), 0);
			gui.showChatMessageArea(0);


			// Initialize Chatlist user actions
			chatUsers.init();
			// Initialize Chatlist private rooms actions
			privateRooms.init();
			privateRooms.addRoom(0, translation.translate('main'), 0);

			// Initialize Chat Aktions Button
			chatActions.init();



			var messageOptions = {
				'recipient':      null,
				'recipient_name': null,
				'public':         1
			};

			// @TODO DONO;
			$('#enter_main').click(function (e) {
				e.preventDefault();
				e.stopPropagation();
				currentRoom = 0;
				$('#chat_messages').ilChatMessageArea('show', 0);
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
				$('#private_rooms_wrapper').css('display', 'none');
			});
			$('#tab_users').click();

			var loader = new ProfileImageLoader($.map(initial.users, function(val) {
				return val.id;
			}), function() {
				$(initial.users).each(function () {
					var tmp = {
						id:    this.id,
						label: this.login,
						type:  'user',
						hide: this.id == personalUserInfo.id,
						image: loader.getProfileImage(this.id)
					};
					$('#chat_users').ilChatUserList('add', tmp, {hide: true});
					userManager.add(tmp, 0);
				});
			});

			// Add initial private rooms to Chatlist
			$(initial.private_rooms).each(function () {
				$('#private_rooms').ilChatList('add', {
					id:    this.proom_id,
					label: this.title,
					type:  'room',
					owner: this.owner
				});
				gui.addChatMessageArea(this.proom_id, this.title, this.owner);
				$('#chat_messages').ilChatMessageArea('addScope', this.proom_id, this);
			});

			// Show private room enter message
			if (initial.enter_room) {
				$('#chat_messages').ilChatMessageArea('show', initial.enter_room, posturl);
				$(initial.messages).each(function () {
					var data = $('#private_rooms').ilChatList('getDataById', this.sub);

					if (this.sub == initial.enter_room && this.entersub == 1 && data) {
						$('#chat_messages').ilChatMessageArea('addMessage', initial.enter_room, {
							type:    this.type,
							message: translation.translate('private_room_entered', {title: data.label})
						});
					}
				});
			}

			// Show initial messages
			$(initial.messages).each(function () {
				var message = this;

				message.timestamp = message.timestamp * 1000;

				if(message.type == 'notice')
				{
					if(message.content == 'connect' && message.data.id == user.id)
					{
						message.content = 'welcome_to_chat';
					}
					message.content = translation.translate(message.content, message.data)
				}
				//if (!this.sub) {
				$('#chat_messages').ilChatMessageArea('addMessage', message.subRoomId || 0, message);
				/*if (this.type == 'connected' || this.type == 'disconnected') {
					if (this.users) {
						$(message.users).each(function () {
							$('#chat_messages').ilChatMessageArea('addMessage', this.sub || 0, message);
						});
					}
				}
				else {
				 $('#chat_messages').ilChatMessageArea('addMessage', this.sub || 0, message);
				}*/
				//}
			});

			// Build more options
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

			// Handle incomming Message
			function handleMessage(message) {
				messageObject = (typeof message == 'object') ? message : $.getAsObject(message);

				//@TODO Debug anders realisieren
				if (typeof DEBUG != 'undefined' && DEBUG) {
					$('#chat_messages').ilChatMessageArea('addMessage', 0, {
						type:    'notice',
						message: messageObject.type
					});
				}

				//@todo was passiert hier?
				if ((!messageObject.sub && subRoomId) || (subRoomId && subRoomId != messageObject.sub)) {
					$('#chat_actions').addClass('chat_new_events');
					var id = typeof messageObject == 'undefined' ? 0 : messageObject.sub;
					var data = $('#private_rooms').ilChatList('getDataById', id);
					if (data) {
						data.new_events = true;
					}
				}
			}

		}
	}(jQuery)
});
