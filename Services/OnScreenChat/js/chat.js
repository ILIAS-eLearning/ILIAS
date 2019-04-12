(function($, $scope, $io) {
	$scope.il.Chat = {
		config: {},
		socket: null,

		setConfig: function(config) {
			getModule().config = config;
		},

		init: function(userId, username, callback) {
			getModule().socket = $io.connect(getModule().config.url, {path: getModule().config.subDirectory});
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
		},

		onConversationInit: function(callback) {
			getModule().socket.on('conversation-init', callback);
		},

		getConversations: function() {
			getModule().socket.emit('conversations');
		},

		sendMessage: function(conversationId, message) {
			getModule().socket.emit('message', conversationId, getModule().config.userId, message);
		},

		getHistory: function(conversationId, oldestMessageTimestamp) {
			getModule().socket.emit('history', conversationId, oldestMessageTimestamp);
		},

		addUser: function(conversationId, userId, name) {
			getModule().socket.emit('addUser', conversationId, userId, name);
		},

		removeUser: function(conversationId, userId, name) {
			getModule().socket.emit('removeUser', conversationId, userId, name);
		},

		onGroupConversationLeft: function(callback) {
			getModule().socket.on('removeUser', callback);
		},

		onGroupConversation: function(callback) {
			getModule().socket.on('addUser', callback);
		},

		receiveMessage: function(callback) {
			getModule().socket.on('message', callback);
		},

		onParticipantsSuppressedMessages: function(callback) {
			getModule().socket.on('participantsSuppressedMessages', callback);
		},

		onSenderSuppressesMessages: function(callback) {
			getModule().socket.on('senderSuppressesMessages', callback);
		},

		receiveConversation: function(callback) {
			getModule().socket.on('conversation', callback);
		},

		trackActivity: function(conversationId, userId, timestamp) {
			getModule().socket.emit('activity', conversationId, userId, timestamp);
		},

		closeConversation: function(conversationId, userId) {
			getModule().socket.emit('closeConversation', conversationId, userId);
		}
	};

	function getModule() {
		return $scope.il.Chat;
	}
})(jQuery, window, io);