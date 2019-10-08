(function($, $scope) {
	'use strict';
	$scope.il.OnScreenChatMenu = {
		config: {},
		rendered: false,
		content: $(''),
		conversations: [],
		messageFormatter: {},
		participantsImages: {},
		participantsNames: {},

		setConfig: function(config) {
			$scope.il.OnScreenChatMenu.config = config;
		},

		setMessageFormatter: function(messageFormatter) {
			$scope.il.OnScreenChatMenu.messageFormatter = messageFormatter;
		},

		getMessageFormatter: function() {
			return getModule().messageFormatter;
		},

		init: function() {
			$('#' + getModule().config.triggerId).popover({
				html : true,
				placement : "bottom",
				viewport : { selector: 'body', padding: 10 },
				title: ' '
			}).on('shown.bs.popover', function () {
				$scope.il.OnScreenChatMenu.show();
			}).on('hidden.bs.popover', function () {
				$("body").removeClass("modal-open");
			});

			$('body').on('click', function (e) {
				$('#' + getModule().config.triggerId).each(function () {
					//the 'is' for buttons that trigger popups
					//the 'has' for icons within a button that triggers a popup
					if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
						if ($(this).next(".popover").length) {
							$(this).popover('hide');
						}
					}
				});
			});
		},

		show: function() {
			var module = $scope.il.OnScreenChatMenu;

			$('#' + getModule().config.triggerId).siblings('.popover').children('.popover-content').html(module.getContent());
			$('body').addClass('modal-open');
			module.afterListUpdate();
		},
		
		close: function() {
			$('#' + getModule().config.triggerId).popover('hide');
		},

		getContent: function() {
			getModule().content = $('#onscreenchatmenu-content-container');

			if (!getModule().rendered) {
				getModule().content.find('#onscreenchatmenu-content').html("");

				var conversations = getModule().conversations.filter(function(conversation){
					return conversation.latestMessage !== null && (conversation.open === false || conversation.open === undefined);
				}).sort(function(a, b) {
					return b.latestMessage.timestamp - a.latestMessage.timestamp;
				});

				var templates = '', conversation_templates = '';

				for (var index in conversations){
					var template = getModule().config.conversationTemplate;
					var latestMessage = conversations[index].latestMessage;
					var participants = conversations[index].participants;
					var participantNames = [], participantUserIds = [];

					for (var key in participants) {
						if(participants.hasOwnProperty(key) && participants[key].id != getModule().config.userId) {
							var publicName = getPublicName(participants[key].id);
							if (publicName !== "") {
								participantNames.push(publicName);
							} else {
								participantNames.push(participants[key].name);
							}

							participantUserIds.push(parseInt(participants[key].id));
						}
					}

					// use Array.includes if IE 11 is dropped
					let is_last_user_in_conversation = $.inArray(parseInt(latestMessage.userId), participantUserIds) !== -1;

					var displayUserId = (function() {
						if (
							latestMessage.userId != getModule().config.userId &&
							latestMessage.userId > 0 &&
							is_last_user_in_conversation
						) {
							return latestMessage.userId;
						} else {
							return participantUserIds[0];
						}
					})();

					template = template.replace('[[avatar]]', getProfileImage(displayUserId));
					template = template.replace('[[userId]]', displayUserId);
					template = template.replace(/\[\[participants\]\]/g, participantNames.join(', '));
					template = template.replace(/\[\[conversationId\]\]/g, conversations[index].id);

					var numNewMessages     = conversations[index].numNewMessages;
					var numMessagesCounter = $(getModule().config.conversationNoveltyCounter).html(numNewMessages);
					if (numNewMessages > 0) {
						numMessagesCounter.addClass('iosOnScreenChatShown');
					} else {
						numMessagesCounter.addClass('iosOnScreenChatHidden');
					}
					template = template.replace("[[badge]]", numMessagesCounter.wrap("<div></div>").parent().html());
					template = template.replace('[[last_message_time]]', momentFromNowToTime(latestMessage.timestamp));
					template = template.replace('[[last_message_time_raw]]', latestMessage.timestamp);
					if (is_last_user_in_conversation) {
						template = template.replace('[[last_message]]', getModule().getMessageFormatter().format(latestMessage.message));
					} else {
						template = template.replace('[[last_message]]', '');
					}

					conversation_templates += template;
				}

				if (getModule().config.showOnScreenChat) {
					templates += '<div class="dropdown-header">' + il.Language.txt('chat_osc_conversations') + '</div>';

					if (getModule().config.showAcceptMessageChange) {
						templates += (getModule().config.infoTemplate).replace('[[html]]', il.Language.txt('chat_osc_dont_accept_msg'));
					}

					if (conversation_templates) {
						templates += conversation_templates;
					} else {
						templates += (getModule().config.infoTemplate).replace('[[html]]', il.Language.txt('chat_osc_no_conv'));
					}
				}

				if (getModule().config.rooms.length > 0) {
					templates += '<div class="dropdown-header">' + il.Language.txt('chat_osc_section_head_other_rooms') + '</div>';
					
					for (var index in getModule().config.rooms) {
						if (getModule().config.rooms.hasOwnProperty(index)) {
							var room = getModule().config.rooms[index],
								template = getModule().config.roomTemplate;

							template = template.replace(/\[\[icon\]\]/g, room.icon);
							template = template.replace(/\[\[url\]\]/g, room.url);
							template = template.replace(/\[\[name\]\]/g, room.name);

							templates += template;
						}
					}
				}

				getModule().content.find('#onscreenchatmenu-content').html(templates);

				il.ExtLink.autolink($('#onscreenchatmenu-content').find('[data-onscreenchat-body-last-msg]'));

				getModule().rendered = true;
			}

			return getModule().content.html();
		},

		addOrUpdate: function(conversation) {
			var index = getModule().hasConversation(conversation);
			if (index === false) {
				getModule().conversations.push(conversation);
			} else {
				getModule().conversations[index] = conversation;
			}

			getModule().rendered = false;
			getModule().updateBadges();
		},

		remove: function(conversation) {
			var index = getModule().hasConversation(conversation);
			if(index !== false) {
				getModule().conversations.splice(index, 1);
				getModule().rendered = false;
				getModule().show();
				getModule().updateBadges();
			}
		},

		updateBadges: function() {
			var conversations = getModule().conversations.filter(function(conversation){
				return conversation.latestMessage != null && (conversation.open === false || conversation.open === undefined);
			});
			var numConversations = conversations.length;
			var numMessages = getModule().countUnreadMessages();

			var messagesBadge = $('[data-onscreenchat-header-menu] .il-counter-novelty');
			var conversationsBadge = $('[data-onscreenchat-header-menu] .il-counter-status');

			conversationsBadge.html(numConversations);
			if (numConversations === 0) {
				conversationsBadge.addClass('iosOnScreenChatHidden').removeClass('iosOnScreenChatShown');
			} else {
				conversationsBadge.removeClass('iosOnScreenChatHidden').addClass('iosOnScreenChatShown');
			}

			messagesBadge.html(numMessages);
			if(numMessages === 0) {
				messagesBadge.addClass('iosOnScreenChatHidden').removeClass('iosOnScreenChatShown');
			} else {
				messagesBadge.removeClass('iosOnScreenChatHidden').addClass('iosOnScreenChatShown');
			}
		},

		syncProfileImages: function(images) {
			getModule().participantsImages = images;
		},

		syncPublicNames: function(names) {
			getModule().participantsNames = names;
		},

		countUnreadMessages: function() {
			var conversations = getModule().conversations;

			var num = 0;
			for(var index in conversations) {
				if(conversations.hasOwnProperty(index) ) {
					num += parseInt(conversations[index].numNewMessages);
				}
			}

			return num;
		},

		afterListUpdate: function() {
			$('.ilOnScreenChatMenuLoader').remove();

			$('#onscreenchatmenu-content').find('[data-toggle="tooltip"]').tooltip({
				container: 'body',
				viewport: { selector: 'body', padding: 10 }
			});
		},

		hasConversation: function(conversation) {
			for (var index in getModule().conversations) {
				if (getModule().conversations.hasOwnProperty(index) && getModule().conversations[index].id == conversation.id) {
					return index;
				}
			}

			return false;
		}
	};

	var getProfileImage = function(userId) {
		if (getModule().participantsImages.hasOwnProperty(userId)) {
			return getModule().participantsImages[userId].src;
		}
		return "";
	};

	var getPublicName = function(userId) {
		if (getModule().participantsNames.hasOwnProperty(userId)) {
			return getModule().participantsNames[userId];
		}

		return "";
	};

	var getModule = function() {
		return $scope.il.OnScreenChatMenu;
	};
})(jQuery, window);