(function($, $scope, $io) {
	$scope.il.Chat = {
		config: {},
		_socket: null,

		setConfig: function(config) {
			getModule().config = config;
		},

		init: function() {
			_socket = $io.connect(getModule().config.url);
			_socket.emit('login', getModule().config.username, getModule().config.userId);
		},

		getConversation: function(participants, callback) {
			_socket.emit('conversation', participants);
			_socket.on('conversation', callback)
		},

		getConversations: function() {
			_socket.emit('conversations');
		},

		sendMessage: function(conversationId, message) {
			_socket.emit('message', conversationId, getModule().config.userId, message);
		},

		getHistory: function(conversationId, callback) {
			_socket.emit('history', conversationId);
			_socket.on('history', callback)
		},

		addParticipant: function() {
			_socket.emit('addParticipant');
		},

		receiveMessage: function(callback) {
			_socket.on('message', callback);
		}
	};

	function getModule() {
		return $scope.il.Chat;
	}
})(jQuery, window, io);