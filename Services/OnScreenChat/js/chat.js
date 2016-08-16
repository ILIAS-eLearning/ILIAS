(function($, $scope, $io) {
	$scope.il.Chat = {
		config: {},
		socket: null,

		setConfig: function(config) {
			getModule().config = config;
		},

		init: function(userId, username, callback) {
			getModule().socket = $io.connect(getModule().config.url);
			getModule().socket.on('connect', function() {
				getModule().login(userId, username, callback);
			});
		},

		onHistory: function(callback) {
			getModule().socket.on('history', callback)
		},

		login: function(userId, username, callback) {
			getModule().socket.emit('login', getModule().config.userId, getModule().config.username);
			getModule().socket.on('login', callback);
		},

		getConversation: function(participants, callback) {
			getModule().socket.emit('conversation', participants);
			getModule().socket.on('conversation', callback)
		},

		getConversations: function() {
			getModule().socket.emit('conversations');
		},

		sendMessage: function(conversationId, message) {
			getModule().socket.emit('message', conversationId, getModule().config.userId, message);
		},

		getHistory: function(conversationId) {
			getModule().socket.emit('history', conversationId);
		},

		addUser: function(conversationId, userId, name, callback) {
			getModule().socket.emit('addUser', conversationId, userId, name);
			getModule().socket.on('addUser', callback);
		},

		receiveMessage: function(callback) {
			getModule().socket.on('message', callback);
		},

		receiveConversation: function(callback) {
			getModule().socket.on('conversation', callback);
		}
	};

	function getModule() {
		return $scope.il.Chat;
	}
})(jQuery, window, io);