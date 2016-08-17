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
					if(getModule().conversations[index].messages.length > 0)
					{
						var participants = getModule().conversations[index].participants;
						var participantNames = [];

						for(var key in participants) {
							if(participants.hasOwnProperty(key) && participants[key].id !== getModule().config.userId) {
								participantNames.push(participants[key].name);
							}
						}

						var template = getModule().config.conversationTemplate;
						template = template.replace('[[avatar]]', 'http://placehold.it/50/FA6F57/fff&amp;text=ME');
						template = template.replace('[[participants]]', participantNames.join(', '));
						template = template.replace(/\[\[conversationId\]\]/, getModule().conversations[index].id);
						template = template.replace('[[last_message]]', getModule().conversations[index].messages[0].message);
						template = template.replace('[[last_message_time]]', momentFromNowToTime(getModule().conversations[index].messages[0].timestamp));
						getModule().content.find('#onscreenchatmenu-content').append(template);
					}
				}
				getModule().rendered = true;
			}

			return getModule().content.html();
		},

		add: function(conversation) {
			if (!getModule().hasConversation(conversation)) {
				getModule().conversations.push(conversation);
				getModule().rendered = false;
			}
		},

		updateList: function() {

		},

		afterListUpdate: function() {
			$('.ilOnScreenChatMenuLoader').remove();
		},

		hasConversation: function(conversation) {
			for(var index in getModule().conversations) {
				if (getModule().conversations.hasOwnProperty(index) && getModule().conversations[index].id == conversation.id) {
					return true;
				}
			}

			return false;
		}
	};

	var getModule = function() {
		return $scope.il.OnScreenChatMenu;
	}
})(jQuery, window);