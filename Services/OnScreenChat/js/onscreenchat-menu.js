(function($, $scope){
	$scope.il.OnScreenChatMenu = {
		config: {},
		rendered: false,
		content: $(''),

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


			$('.ilOnScreenChatMenuDropDown').on('click', '.media', function() {
				$scope.il.OnScreenChat.open();
				$('#onscreenchat_trigger').popover('hide');
			});
		},

		show: function() {
			var module = $scope.il.OnScreenChatMenu;

			$('#onscreenchat_trigger').siblings('.popover').children('.popover-content').html(module.getContent());
			$('body').addClass('modal-open');
			module.afterListUpdate();
		},

		getContent: function() {
			var module = $scope.il.OnScreenChatMenu;
			module.content = $('#onscreenchatmenu-content-container');

			if(!module.rendered)
			{
				for(var i = 0; i < 5; i++)
				{
					var template = module.config.conversationTemplate;
					template = template.replace('[[avatar]]', 'http://placehold.it/50/FA6F57/fff&amp;text=ME');
					template = template.replace('[[public_username]]', 'Thomas Joußen');
					template = template.replace('[[username]]', 'tjoussen' + i);
					template = template.replace('[[last_message]]', 'Hast du dir den Kursinhalt in Lineare Algebra schon angesehen?');
					template = template.replace('[[last_message_time]]', momentFromNowToTime("03.08.2016 11:43", "DD.MM.YYYY H:mm"));
					module.content.find('#onscreenchatmenu-content').append(template);
				}
				module.rendered = true;
			}

			return module.content.html();
		},

		updateList: function() {

		},

		afterListUpdate: function() {
			$('.ilOnScreenChatMenuLoader').remove();
		}
	}
})(jQuery, window);