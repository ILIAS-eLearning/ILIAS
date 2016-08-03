(function($, $scope){
	$scope.il.OnScreenChat = {
		config: {},
		container: undefined,
		windows: [],

		setConfig: function(config) {
			$scope.il.OnScreenChat.config = config;
		},

		init: function() {
			var module = $scope.il.OnScreenChat;

			module.container = $('<div></div>').addClass('row');

			$('body').append(
				$('<div></div>')
					.attr('id', 'onscreenchat-container')
					.addClass('container')
					.append(module.container)
			);
		},

		open: function() {
			$scope.il.OnScreenChat.createWindow();
		},

		createWindow: function() {
			var participants = ['Michael Jansen'];
			var module = $scope.il.OnScreenChat;
			var template = module.config.chatWindowTemplate;

			template = template.replace('[[participants]]', participants.join(', '));
			template = $(template);

			module.windows.push(template);
			module.container.append(template);

			$('.close').on('click', function() {
				$scope.il.OnScreenChat.close($(this).closest('.chat-window-wrapper'));

			});
			$('.chat-window-wrapper').on('click', '.submit', function() {
				$scope.il.OnScreenChat.handleSubmit($(this));
			}).on('keydown', function(e) {
				if(e && e.keyCode == 13 && !e.shiftKey) {
					$scope.il.OnScreenChat.handleSubmit($(this));
				}
			});

			$('textarea').on('keydown', function(e) {
				if(e.which == 13 && !e.shiftKey) {
					e.preventDefault();
				}
			}).on('input', function() {
				$scope.il.OnScreenChat.resizeInput($(this));
			});
		},

		addMessage: function(window, from, message) {
			var module = $scope.il.OnScreenChat;
			var template = module.config.messageTemplate;

			message = message.replace(/(?:\r\n|\r|\n)/g, '<br />');

			template = template.replace(/\[\[username\]\]/g, 'USERNAME');
			template = template.replace(/\[\[time\]\]/g, '1 Hour ago');
			template = template.replace(/\[\[message]\]/g, message);
			template = template.replace(/\[\[avatar\]\]/g, (from == '.left')? 'http://placehold.it/50/55C1E7/fff&amp;text=U': 'http://placehold.it/50/FA6F57/fff&amp;text=ME');

			template = $(template).find('li.' + from).html();

			$(window).find('.chat').append(
				$('<li></li>')
					.addClass(from)
					.addClass('clearfix')
					.append(template)
			);
		},

		close: function(window) {
			$(window).remove();
		},

		handleSubmit: function(trigger) {
			var window = $(trigger).closest('.chat-window-wrapper');

			$scope.il.OnScreenChat.send(window);
			$scope.il.OnScreenChat.resizeInput(window.find('.chat-message'));
		},

		send: function(window) {
			var message = $(window).find('.chat-message').val();
			if(message != "")
			{
				$scope.il.OnScreenChat.addMessage(window, 'right', message);
				$(window).find('.chat-message').val('');
			}
		},

		resizeInput: function(input) {
			$(input).height(1);
			var totalHeight = $(input).prop('scrollHeight') - parseInt($(input).css('padding-top')) - parseInt($(input).css('padding-bottom'));
			$(input).height(totalHeight);
		}
	}
})(jQuery, window);