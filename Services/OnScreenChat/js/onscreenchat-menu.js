(function($, $scope){
	$scope.il.OnScreenChatMenu = {
		config: {},
		rendered: false,
		content: $(''),
		conversations: [],

		setConfig: function(config) {
			$scope.il.OnScreenChatMenu.config = config;
		},

		init: function() {
			$('#onscreenchat_trigger').popover({
				html : true,
				placement : "bottom",
				viewport : { selector: 'body', padding: 10 },
				title: "Conversation"
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
				for(var index in getModule().conversations){
					if(getModule().conversations[index].latestMessage != null)
					{
						console.log(getModule().conversations[index]);
						var template = getModule().config.conversationTemplate;
						var latestMessage = getModule().conversations[index].latestMessage;
						var participants = getModule().conversations[index].participants;
						var participantNames = [];

						for(var key in participants) {
							if(participants.hasOwnProperty(key) && participants[key].id !== getModule().config.userId) {
								participantNames.push(participants[key].name);
							}
						}

						template = template.replace('[[avatar]]', 'http://placehold.it/50/FA6F57/fff&amp;text=ME');
						template = template.replace('[[participants]]', participantNames.join(', '));
						template = template.replace(/\[\[conversationId\]\]/, getModule().conversations[index].id);
						template = template.replace('[[last_message]]', latestMessage.message);
						template = template.replace('[[last_message_time]]', momentFromNowToTime(latestMessage.timestamp));
						getModule().content.find('#onscreenchatmenu-content').append(template);
					}
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

			getModule().rendered = false;
			getModule().updateBadges();
		},

		updateBadges: function() {
			var numConversations = getModule().conversations.length;
			var numMessages = getModule().countUnreadMessages();
			var conversationsBadge = $('[data-onscreenchat-menu-numconversations]');
			var messagesBadge = $('[data-onscreenchat-menu-nummessages]');

			conversationsBadge.html(numConversations);
			if(numConversations == 0) {
				conversationsBadge.hide();
			} else {
				conversationsBadge.show();
			}

			messagesBadge.html(numMessages);
			if(numMessages == 0) {
				messagesBadge.hide();
			} else {
				messagesBadge.show();
			}
		},

		countUnreadMessages: function() {
			var conversations = getModule().conversations;

			var num = 0;
			for(var index in conversations) {
				if(conversations.hasOwnProperty(index) ) {
					num += parseInt(conversations[index].numNewMessages);
				}
			}

			console.log(num);
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

	var getModule = function() {
		return $scope.il.OnScreenChatMenu;
	}
})(jQuery, window);