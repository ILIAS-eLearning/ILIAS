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

			module.createWindow();
			module.createWindow();
		},

		createWindow: function() {
			var participants = ['Thomas Joußen', 'Michael Jansen'];
			var module = $scope.il.OnScreenChat;
			var template = module.config.chatWindowTemplate;

			template = template.replace('[[participants]]', participants.join(', '));

			module.windows.push(template);
			module.container.append(template);

			module.addMessage();
			module.addMessage();
			module.addMessage();
		},

		addMessage: function() {
			var module = $scope.il.OnScreenChat;
			var template = module.config.messageTemplate;

			template = template.replace('[[username]]', 'USERNAME');
			template = template.replace('[[time]]', '1 Hour ago');
			template = template.replace('[[message]]', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec vehicula rhoncus pharetra. Aliquam finibus purus in orci ultrices rutrum. Ut placerat cursus ligula. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas accumsan est sit amet pulvinar elementum. Sed viverra nibh velit, vel elementum lectus posuere id.');

			module.windows[0].find('.chat').append(template);
		}


	}
})(jQuery, window);