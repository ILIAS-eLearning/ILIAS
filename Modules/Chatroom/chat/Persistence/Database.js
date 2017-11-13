var Container = require('../AppContainer');
var async = require('async');
var Date = require('../Helper/Date');
var UUID = require('node-uuid');

var Database = function Database(config) {

	var _pool;

	var handleError = function(err){
		if(err) {
			throw err;
		}
	};

	this.connect = function(callback) {
		var engine = require(config.database.type);

		_pool = engine.createPool({
			host: config.database.host,
			port: config.database.port,
			user: config.database.user,
			password: config.database.pass,
			database: config.database.name,
			charset: 'UTF8_UNICODE_CI'
			//debug: true
		});

		_pool.getConnection(callback);
	};

	this.closePrivateRoom = function(roomId){
		var time = parseInt(Date.getTimestamp()/1000);

		_pool.query('UPDATE chatroom_prooms SET closed = ? WHERE proom_id = ?',
			[time, roomId],
			handleError
		);
	};

	this.disconnectAllUsers = function(callback) {

		callback = callback || function(){};
		var time = parseInt(Date.getTimestamp()/1000);
		// Disconnect from private rooms
		_pool.query('UPDATE chatroom_psessions SET disconnected = ?',
			[time],
			handleError
		);

		_pool.query('UPDATE chatroom_prooms SET closed = ? WHERE closed = 0',
			[time],
			handleError
		);

		var onErrorWithCallback = function(err){
			if(err) {
				throw err;
			}

			Container.getLogger().info('Successfully disconnected all users from server');
			callback();
		};

		async.waterfall([
			function(next){
				var onError = function(err, result){
					if(err) {
						throw err;
					}

					next(err, result);
				};

				_pool.query(
					'SELECT * FROM chatroom_users',
					onError
				);
			},
			function(result, next)
			{
				var onError = function(err){
					if(err) {
						throw err;
					}
					next();
				};

				async.eachSeries(result, function(element, nextLoop){
					_getNextId('chatroom_sessions', function(sessionId) {
						var onError = function(err){
							if(err) {
								throw err;
							}
							nextLoop();
						};

						_pool.query('INSERT INTO chatroom_sessions SET ?',
							{
								sess_id: sessionId,
								room_id: element.room_id,
								user_id: element.user_id,
								userdata: element.userdata,
								connected: element.connected,
								disconnected: time
							},
							onError
						);
					});
				},
				onError);
			},
			function(next) {
				// Disconnect from chat
				var onError = function(err){
					if(err) {
						throw err;
					}

					next();
				};

				_pool.query('DELETE FROM chatroom_users',
					onError
				);
			}
		], onErrorWithCallback);
	};

	this.disconnectUser = function(subscriber, roomIds, subRoomIds) {
		var time = parseInt(Date.getTimestamp()/1000);

		// Disconnect from private rooms
		if(subRoomIds.length > 0 )
		{
			_pool.query(
				'UPDATE chatroom_psessions SET disconnected = ? WHERE user_id = ? AND proom_id IN (?)',
				[time, subscriber.getId(), subRoomIds],
				handleError
			);
		}

		// Write chat_session

		if(roomIds.length > 0)
		{
			async.waterfall([
				function(next){
					var onError = function(err, result){
						if(err) {
							throw err;
						}

						next(null, result);
					};

					_pool.query(
						'SELECT * FROM chatroom_users WHERE user_id = ? AND room_id IN (?)',
						[subscriber.getId(), roomIds],
						onError
					);
				},
				function(result, next)
				{
					var onError = function(err){
						if(err) {
							throw err;
						}
						next();
					};

					async.eachSeries(result, function(element, nextLoop){
						_getNextId('chatroom_sessions', function(sessionId) {
							var onError = function(err){
								if(err) {
									throw err;
								}
								nextLoop();
							};
							_pool.query('INSERT INTO chatroom_sessions SET ?',
								{
									sess_id: sessionId,
									room_id: element.room_id,
									user_id: subscriber.getId(),
									userdata: element.userdata,
									connected: element.connected,
									disconnected: time
								},
								onError
							);
						});
					},
					onError);
				},
				function(next) {
					var onError = function(err){
						if(err) {
							throw err;
						}

						next();
					};

					_pool.query('DELETE FROM chatroom_users WHERE user_id = ? AND room_id IN (?)',
						[subscriber.getId(), roomIds],
						onError
					);
				}
			], onError);
		}

	};

	this.addHistory = function(message) {
		this.persistMessage(message);
	};

	/**
	 *
	 * @param {Message} message
	 */
	this.persistMessage = function(message) {
		_getNextId('chatroom_history', function(id){
			message.timestamp = parseInt(message.timestamp / 1000);

			_pool.query('INSERT INTO chatroom_history SET ?', {
				hist_id: id,
				room_id: message.roomId,
				message: JSON.stringify(message),
				timestamp: message.timestamp, // Eventuell hier durch 1000 teilen für PHP. Timestamp in JSON dann für JS benutzen
				sub_room: message.subRoomId
			}, handleError);
		})
	};

	this.getMessageAcceptanceStatusForUsers = function(onResult, onEnd) {
		_onQueryEvents(
			_pool.query('SELECT usr_id FROM usr_pref WHERE keyword = ? AND value = ?', ["chat_osc_accept_msg", "y"]),
			onResult,
			onEnd
		);
	};

	this.clearChatMessagesProcess = function (bound, namespaceName, callback) {
		bound = parseInt(bound / 1000);

		var onError = function(err){
			if(err) {
				throw err;
			}

			callback();
		};

		async.waterfall([
			function(next) {
				var onError = function (err, result) {
					if (err) {
						throw err;
					}
					Container.getLogger().info("Clear Messages for namespace %s affected %s rows", namespaceName, result.affectedRows)

					next(null, result);
				};

				_pool.query('DELETE FROM chatroom_history WHERE timestamp < ?',
					[bound],
					onError
				);
			},
			function(result, next)
			{
				var onError = function (err, result) {
					if (err) {
						throw err;
					}
					Container.getLogger().info("Clear OSC-Messages for namespace %s affected %s rows", namespaceName, result.affectedRows)

					next(null, result);
				};

				_pool.query('DELETE FROM osc_messages WHERE timestamp < ?',
					[bound],
					onError
				);
			},
			function(result, next)
			{
				var onError = function (err, result) {
					if (err) {
						throw err;
					}
					Container.getLogger().info("Clear OSC-Conversations for namespace %s affected %s rows", namespaceName, result.affectedRows)

					next(null, result);
				};

				_pool.query('DELETE c FROM osc_conversation c LEFT JOIN osc_messages m ON m.conversation_id = c.id WHERE m.id IS NULL',
					[bound],
					onError);
			},
			function(result, next)
			{
				var onError = function (err, result) {
					if (err) {
						throw err;
					}
					Container.getLogger().info("Clear OSC-Activity for namespace %s affected %s rows", namespaceName, result.affectedRows)

					next(null, result);
				};

				_pool.query('DELETE a FROM osc_activity a LEFT JOIN osc_conversation c ON a.conversation_id = c.id WHERE c.id IS NULL',
					[bound],
					onError
				);
			}
		], onError);
	};

	this.trackActivity = function(conversationId, userId, timestamp) {
		var emptyResult = true;
		_onQueryEvents(
			_pool.query('SELECT * FROM osc_activity WHERE conversation_id = ? AND user_id = ?', [conversationId, userId]),
			function(result){
				emptyResult = false;
				if(timestamp > 0) {
					_pool.query('UPDATE osc_activity SET timestamp = ?, is_closed = ? WHERE conversation_id = ? AND user_id = ?',
						[timestamp, 0, conversationId, userId],
						handleError
					);
				}
			},
			function() {
				if(emptyResult)
				{
					_pool.query('INSERT INTO osc_activity SET ?', {
						conversation_id: conversationId,
						user_id: userId,
						timestamp: timestamp
					}, handleError());
				}
			}
		);
	};

	this.closeConversation = function(conversationId, userId) {
		_onQueryEvents(
			_pool.query('SELECT * FROM osc_activity WHERE conversation_id = ? AND user_id = ?', [conversationId, userId]),
			function(result){
				_pool.query('UPDATE osc_activity SET is_closed = ? WHERE conversation_id = ? AND user_id = ?',
					[1, conversationId, userId],
					handleError
				);
			},
			function() {
			}
		);
	};

	this.getConversationStateForParticipant = function(conversationId, userId, onResult, onEnd) {
		_onQueryEvents(
			_pool.query('SELECT * FROM osc_activity WHERE conversation_id = ? AND user_id = ?', [conversationId, userId]),
			onResult,
			onEnd
		);
	};

	/**
	 *
	 * @param message
	 */
	this.persistConversationMessage = function(message) {
		//message.timestamp = parseInt(message.timestamp / 1000);

		_pool.query('INSERT INTO osc_messages SET ?', {
			id: UUID.v4(),
			conversation_id: message.conversationId,
			user_id: message.userId,
			message: message.message,
			timestamp: message.timestamp
		}, handleError);
	};

	this.loadConversations = function(onResult, onEnd) {
		_onQueryEvents(
			_pool.query('SELECT * FROM osc_conversation'),
			onResult,
			onEnd
		);
	};

	this.getLatestMessage = function(conversation, onResult, onEnd) {
		_onQueryEvents(
			_pool.query('SELECT * FROM osc_messages WHERE conversation_id = ? ORDER BY timestamp DESC LIMIT 1', [conversation.getId()]),
			onResult,
			onEnd
		);
	};

	this.countUnreadMessages = function(conversationId, userId, onResult, onEnd) {
		_onQueryEvents(
			_pool.query('SELECT COUNT(m.id) AS numMessages FROM osc_messages m LEFT JOIN osc_activity a ON a.conversation_id = m.conversation_id WHERE m.conversation_id = ? AND a.user_id = ? AND m.timestamp > a.timestamp', [conversationId, userId]),
			onResult,
			onEnd
		);
	};

	this.loadConversationHistory = function(conversationId, oldestMessageTimestamp, onResult, onEnd){
		var query = 'SELECT * FROM osc_messages WHERE conversation_id = ?';
		var params = [conversationId];
		if(oldestMessageTimestamp != null)
		{
			query += ' AND timestamp < ?';
			params.push(oldestMessageTimestamp);
		}

		query += " ORDER BY timestamp DESC LIMIT 0, 6";

		_onQueryEvents(
			_pool.query(query, params),
			onResult,
			onEnd
		);
	};

	/**
	 * @param {Conversation} conversation
	 */
	this.updateConversation = function(conversation) {
		var participantsJson = [];
		var participants = conversation.getParticipants();

		for(var index in participants) {
			if(participants.hasOwnProperty(index)){
				participantsJson.push(participants[index].json())
			}
		}
		participantsJson = JSON.stringify(participantsJson);

		_pool.query('UPDATE osc_conversation SET participants = ?, is_group = ? WHERE id = ?',
			[participantsJson, conversation.isGroup(), conversation.getId()],
			handleError
		);
	};


	/**
	 * @param {Conversation} conversation
	 */
	this.persistConversation = function(conversation) {
		_pool.query(
			'INSERT INTO osc_conversation SET ?',
			{
				id: conversation.getId(),
				is_group: conversation.isGroup(),
				participants: JSON.stringify(conversation.getParticipants())
			},
			handleError
		);
	};


	this.loadScopes = function(onResult, onEnd) {
		_onQueryEvents(
			_pool.query('SELECT room_id FROM chatroom_settings'),
			onResult,
			onEnd
		);
	};

	this.loadSubScopes = function(onResult, onEnd) {
		_onQueryEvents(
			_pool.query('SELECT proom_id, parent_id, title, owner FROM chatroom_prooms roomtable WHERE closed = 0 OR closed IS NULL'),
			onResult,
			onEnd
		);
	};

	this.getConnection = function(callback) {
		_pool.getConnection(callback);
	};

	function _onQueryEvents(query, onResult, onEnd) {
		query.on('result', onResult);
		query.on('end', onEnd);
	}

	function _getNextId(tableName, callback) {
		var onError = function(err, insertId) {
			if(err) {
				throw err;
			}

			callback(insertId);
		};

		async.waterfall([
			function(next) {
				var onError = function(err, result){
					if(err) {
						throw err;
					}

					next(null, result.insertId);
				};

				_pool.query('INSERT INTO '+tableName+'_seq (sequence) VALUES (NULL)', [], onError);
			},
			function(insertId, next) {
				var onError = function(err) {
					if(err) {
						throw err;
					}

					next(null, insertId);
				};

				_pool.query('DELETE FROM '+tableName+'_seq WHERE sequence < ?', [insertId], onError);
			}
		], onError);
	}
};


module.exports = Database;