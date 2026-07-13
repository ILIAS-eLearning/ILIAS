/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

const Container = require('../AppContainer');
const sync = require('../Helper/sync');
const Date = require('../Helper/Date');
const engine = require('mariadb/callback');

var Database = function Database(config) {

	var _pool;

	function handleError(err){
		if(err) {
			throw err;
		}
	};

	this.connect = function(callback) {
		_pool = engine.createPool({
			host: config.database.host,
			port: config.database.port,
			user: config.database.user,
			password: config.database.pass,
			database: config.database.name,
		        collation: 'UTF8_UNICODE_CI',
                        insertIdAsNumber: true,
                        bigIntAsNumber: true,
                        // decimalAsNumber: true,
			// debug: true,
		});

		_pool.getConnection(callback);
	};

	this.disconnectAllUsers = function(callback) {

		callback = callback || (() => {});
		const time = parseInt(Date.getTimestamp() / 1000);

		function fetchUsers(next){
			function onError(err, result){
				if(err) {
					throw err;
				}

				next(err, result);
			};

			_pool.query('SELECT * FROM chatroom_users', onError);
		}

		function deleteChatroomUsers(next) {
			// Disconnect from chat
			var onError = function onError(err){
				if(err) {
					throw err;
				}

				next();
			};

			_pool.query('DELETE FROM chatroom_users',
				onError
			);
		}

		const p = sync.toPromise(fetchUsers)()
		      .then(sync.toPromise(createChatRoomSession.bind(null, time)))
		      .then(() => sync.toPromise(deleteChatroomUsers)())
		      .then(() => Container.getLogger().info('Successfully disconnected all users from server'))

		sync.fromPromise(p, callback);
	};

	this.disconnectUser = function(subscriber, roomIds) {
		const time = parseInt(Date.getTimestamp() / 1000);

		// Write chat_session

		if(roomIds.length > 0)
		{
			function fetchChatroomUsers(next){
				var onError = function onError(err, result){
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
			}

			function deleteChatroomUsers(next) {
				var onError = function onError(err){
					if(err) {
						throw err;
					}

					next();
				};

				_pool.query('DELETE FROM chatroom_users WHERE user_id = ? AND room_id IN (?)',
					[subscriber.getId(), roomIds],
					onError
				);
			};

			sync.toPromise(fetchChatroomUsers)()
				.then(sync.toPromise(createChatRoomSession.bind(null, time)))
				.then(() => sync.toPromise(deleteChatroomUsers)());
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
		var onId = function(id){
			message.timestamp = parseInt(message.timestamp / 1000);

			insert('chatroom_history', {
				hist_id: id,
				room_id: message.roomId,
				message: JSON.stringify(message),
				timestamp: message.timestamp, // Eventuell hier durch 1000 teilen für PHP. Timestamp in JSON dann für JS benutzen
			}, handleError);
		};

		_getNextId('chatroom_history', onId);
	};

	this.getMessageAcceptanceStatusForUsers = function(onResult, onEnd) {
		queryIterated(
			'SELECT usr_id FROM usr_pref WHERE keyword = ? AND value = ?',
                        ['chat_osc_accept_msg', 'y'],
			onResult,
			onEnd
		);
	};

	this.clearChatMessagesProcess = function(bound, namespaceName, callback) {
		var boundMilliseconds = parseInt(bound),
			boundSeconds      = parseInt(bound / 1000);

		function clearMessagesFromNamespace(next) {
			function onClear(err, result) {
				if (err) {
					throw err;
				}
				Container.getLogger().info("Clear Messages for namespace %s affected %s rows", namespaceName, result.affectedRows)

				next(null, result);
			}

			_pool.query('DELETE FROM chatroom_history WHERE timestamp < ?',
				[boundSeconds],
				onClear
			);
		}

		function clearOscMessagesFromNamespace(result, next)
		{
			function onClear(err, result) {
				if (err) {
					throw err;
				}
				Container.getLogger().info("Clear OSC-Messages for namespace %s affected %s rows", namespaceName, result.affectedRows)

				next(null, result);
			}

			_pool.query('DELETE FROM osc_messages WHERE timestamp < ?',
				[boundMilliseconds],
				onClear
			);
		}

		function clearOscConversations(result, next)
		{
			function onClear(err, result) {
				if (err) {
					throw err;
				}
				Container.getLogger().info("Clear OSC-Conversations for namespace %s affected %s rows", namespaceName, result.affectedRows)

				next(null, result);
			}

			_pool.query(
				'DELETE c FROM osc_conversation c LEFT JOIN osc_messages m ON m.conversation_id = c.id WHERE m.id IS NULL',
				[boundMilliseconds],
				onClear
			);
		}

		function clearOscActivity(result, next)
		{
			var onClear = function onClear(err, result) {
				if (err) {
					throw err;
				}
				Container.getLogger().info("Clear OSC-Activity for namespace %s affected %s rows", namespaceName, result.affectedRows)

				next(null, result);
			};

			_pool.query('DELETE a FROM osc_activity a LEFT JOIN osc_conversation c ON a.conversation_id = c.id WHERE c.id IS NULL',
				[boundMilliseconds],
				onClear
			);
		}

		const p = sync.toPromise(clearMessagesFromNamespace)()
		      .then(sync.toPromise(clearOscMessagesFromNamespace))
		      .then(sync.toPromise(clearOscConversations))
		      .then(sync.toPromise(clearOscActivity));

		sync.fromPromise(p, callback);
	};

	this.trackActivity = function(conversationId, userId, timestamp) {
		var emptyResult = true;

		function onResult(result){
			emptyResult = false;
			if(timestamp > 0) {
				_pool.query('UPDATE osc_activity SET timestamp = ?, is_closed = ? WHERE conversation_id = ? AND user_id = ?',
					[timestamp, 0, conversationId, userId],
					handleError
				);
			}
		}

		function onEnd() {
			if(emptyResult)
			{
				insert('osc_activity', {
					conversation_id: conversationId,
					user_id: userId,
					timestamp: timestamp
				}, handleError);
			}
		}

		queryIterated(
                        'SELECT * FROM osc_activity WHERE conversation_id = ? AND user_id = ?',
                        [conversationId, userId],
			onResult,
			onEnd
		);
	};

	this.closeConversation = function(conversationId, userId) {
		function onResult(result){
			_pool.query('UPDATE osc_activity SET is_closed = ?, timestamp = ? WHERE conversation_id = ? AND user_id = ?',
				[1, Date.getTimestamp(), conversationId, userId],
				handleError
			);
		}

		function onNull() {
		}

		queryIterated(
			'SELECT * FROM osc_activity WHERE conversation_id = ? AND user_id = ?',
                        [conversationId, userId],
			onResult,
			onNull
		);
	};

	this.getConversationStateForParticipant = function(conversationId, userId, onResult, onEnd) {
		queryIterated(
			'SELECT * FROM osc_activity WHERE conversation_id = ? AND user_id = ?',
                        [conversationId, userId],
			onResult,
			onEnd
		);
	};

	/**
	 *
	 * @param message
	 */
	this.persistConversationMessage = function(message) {
		insert('osc_messages', {
			id: message.id,
			conversation_id: message.conversationId,
			user_id: message.userId,
			message: message.message,
			timestamp: message.timestamp
		}, handleError);
	};

	this.loadConversations = function(onResult, onEnd) {
		queryIterated(
			'SELECT * FROM osc_conversation',
                        [],
			onResult,
			onEnd
		);
	};

	this.getLatestMessage = function(conversation, onResult, onEnd) {
		queryIterated(
			'SELECT * FROM osc_messages WHERE conversation_id = ? ORDER BY timestamp DESC LIMIT 1',
                        [conversation.getId()],
			onResult,
			onEnd
		);
	};

	this.countUnreadMessages = function(conversationId, userId, onResult, onEnd) {
		queryIterated(
			'SELECT COUNT(m.id) AS numMessages FROM osc_messages m LEFT JOIN osc_activity a ON a.conversation_id = m.conversation_id WHERE m.conversation_id = ? AND a.user_id = ? AND m.timestamp > a.timestamp',
                        [conversationId, userId],
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

		queryIterated(
			query,
                        params,
			onResult,
			onEnd
		);
	};

	/**
	 * @param {Conversation} conversation
	 */
	this.updateConversation = function(conversation) {
		var participantsJson = JSON.stringify(getConversationParticipantsJson(conversation));

		_pool.query('UPDATE osc_conversation SET participants = ?, is_group = ? WHERE id = ?',
			[participantsJson, conversation.isGroup(), conversation.getId()],
			handleError
		);
	};


	/**
	 * @param {Conversation} conversation
	 */
	this.persistConversation = function(conversation) {
		var participantsJson = JSON.stringify(getConversationParticipantsJson(conversation));

		insert(
			'osc_conversation',
			{
				id: conversation.getId(),
				is_group: conversation.isGroup(),
				participants: participantsJson
			},
			handleError
		);
	};


	this.loadScopes = function(onResult, onEnd) {
		queryIterated(
			'SELECT room_id FROM chatroom_settings',
                        [],
			onResult,
			onEnd
		);
	};

	this.getConnection = function(callback) {
		_pool.getConnection(callback);
	};

	function getConversationParticipantsJson(conversation) {
		var participantsJson = {};
		var participants = conversation.getParticipants();

		for (var index in participants) {
			if(participants.hasOwnProperty(index)) {
				var p = participants[index];
				if (p.getId() !== null && p.getId() > 0 && !participantsJson.hasOwnProperty(p.getId())) {
					participantsJson[p.getId()] = p.json();
				}
			}
		}

		if (typeof Object.values === "function") {
			return Object.values(participantsJson);
		} else {
			return Object.keys(participantsJson).map(function(k) {
				return participantsJson[k]
			});
		}
	}

        function createChatRoomSession(time, result, next)
	{
		function onNext(element, nextLoop){
			_getNextId('chatroom_sessions', sessionId => {
				insert(
                                        'chatroom_sessions',
                                        {
						sess_id: sessionId,
						room_id: element.room_id,
						user_id: element.user_id,
						userdata: element.userdata,
						connected: element.connected,
						disconnected: time
					},
					err => {
                                                if (err) {
                                                        throw err;
                                                }
                                                nextLoop();
                                        }
                                );
			});
		}


		sync.fromPromise(sync.each(result, onNext), next);
	}

        function insert(table, data, then)
        {
                const qm = ', ?'.repeat(Object.values(data).length).substring(2);
          _pool.query(
            `INSERT INTO ${table} (${Object.keys(data).map(_pool.escapeId.bind(_pool))}) VALUES (${qm})`,
            Object.values(data),
            then
          );
        }

        function queryIterated(query, args, onResult, onEnd) {
                _pool.query(query, args, (err, rows) => {
                        if (err) {
                                throw err;
                        }
                        rows.forEach(onResult);
                        onEnd();
                });
        }

	function _getNextId(tableName, callback) {
		var insertSequence = function(next) {
			function onError(err, result){
				if(err) {
					throw err;
				}

				next(null, result.insertId);
			}

			_pool.query('INSERT INTO '+tableName+'_seq (sequence) VALUES (NULL)', [], onError);
		};

		function deleteSequence(insertId, next) {
			function onError(err) {
				if(err) {
					throw err;
				}

				next(null, insertId);
			}

			_pool.query('DELETE FROM '+tableName+'_seq WHERE sequence < ?', [insertId], onError);
		}

	  sync.toPromise(insertSequence)().then(sync.toPromise(deleteSequence)).then(callback);
	}
};

module.exports = Database;
