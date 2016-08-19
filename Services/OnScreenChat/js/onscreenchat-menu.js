(function($, $scope){
	$scope.il.OnScreenChatMenu = {
		config: {},
		rendered: false,
		content: $(''),
		conversations: [],
		emoticons: {},
		participantsImages: {},

		setConfig: function(config) {
			$scope.il.OnScreenChatMenu.config = config;
		},

		setEmoticons: function(emoticons) {
			$scope.il.OnScreenChatMenu.emoticons = emoticons;
		},

		getEmoticons: function() {
			return getModule().emoticons;
		},

		init: function() {
			$('#onscreenchat_trigger').popover({
				html : true,
				placement : "bottom",
				viewport : { selector: 'body', padding: 10 },
				title: il.Language.txt('chat_osc_conversations')
			}).on('shown.bs.popover', function () {
				$scope.il.OnScreenChatMenu.show();
			}).on('hidden.bs.popover', function () {
				$("body").removeClass("modal-open");
			});



			$('body').on('click', function (e) {
				$('#onscreenchat_trigger[data-toggle="popover"]').each(function () {
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

			$('#onscreenchat_trigger').siblings('.popover').children('.popover-content').html(module.getContent());
			$('body').addClass('modal-open');
			module.afterListUpdate();
		},

		getContent: function() {
			getModule().content = $('#onscreenchatmenu-content-container');

			if(!getModule().rendered)
			{
				getModule().content.find('#onscreenchatmenu-content').html("");

				var conversations = getModule().conversations.filter(function(conversation){
					return conversation.latestMessage != null && (conversation.open === false || conversation.open == undefined);
				}).sort(function(a, b) {
					return b.latestMessage.timestamp - a.latestMessage.timestamp;
				});

				for(var index in conversations){
					var template = getModule().config.conversationTemplate;
					var latestMessage = conversations[index].latestMessage;
					var participants = conversations[index].participants;
					var participantNames = [];

					for(var key in participants) {
						if(participants.hasOwnProperty(key) && participants[key].id !== getModule().config.userId) {
							participantNames.push(participants[key].name);
						}
					}

					template = template.replace('[[avatar]]', getProfileImage(participants[0].id));
					template = template.replace('[[userId]]', participants[0].id);
					template = template.replace(/\[\[participants\]\]/g, participantNames.join(', '));
					template = template.replace(/\[\[conversationId\]\]/, conversations[index].id);
					template = template.replace('[[last_message]]', getModule().getEmoticons().replace(latestMessage.message));
					template = template.replace('[[last_message_time]]', momentFromNowToTime(latestMessage.timestamp));
					template = template.replace('[[last_message_time_raw]]', latestMessage.timestamp);

					getModule().content.find('#onscreenchatmenu-content').append(template);
				}
				getModule().rendered = true;
			}

			return getModule().content.html();
		},

		add: function(conversation) {
			var index = getModule().hasConversation(conversation);
			if (index === false) {
				getModule().conversations.push(conversation);
			} else {
				getModule().conversations[index] = conversation;
			}

			console.log(conversation);

			getModule().rendered = false;
			getModule().updateBadges();
		},

		updateBadges: function() {
			var conversations = getModule().conversations.filter(function(conversation){
				return conversation.latestMessage != null && (conversation.open === false || conversation.open == undefined);
			});
			var numConversations = conversations.length;
			var numMessages = getModule().countUnreadMessages();
			var conversationsBadge = $('[data-onscreenchat-menu-numconversations]');
			var messagesBadge = $('[data-onscreenchat-menu-nummessages]').hide();

			console.log(numConversations);
			conversationsBadge.html(numConversations);
			if(numConversations == 0) {
				conversationsBadge.hide();
			} else {
				conversationsBadge.show();
			}

			/*messagesBadge.html(numMessages);
			if(numMessages == 0) {
				messagesBadge.hide();
			} else {
				messagesBadge.show();
			}*/
		},

		syncProfileImages: function(images) {
			getModule().participantsImages = images;
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
		},

		hasConversation: function(conversation) {
			for(var index in getModule().conversations) {
				if (getModule().conversations.hasOwnProperty(index) && getModule().conversations[index].id == conversation.id) {
					return index;
				}
			}

			return false;
		}
	};

	var getProfileImage = function(userId) {
		if(getModule().participantsImages.hasOwnProperty(userId)) {
			return getModule().participantsImages[userId].src;
		}
		return "";
	};

	var getModule = function() {
		return $scope.il.OnScreenChatMenu;
	};
})(jQuery, window);