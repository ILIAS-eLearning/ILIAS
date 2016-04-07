var Container = require('../AppContainer');
var async = require('async');
var Date = require('../Helper/Date');

var Database = function Database(config) {

	var _pool;

	this.connect = function(callback) {
		var engine = require(config.database.type);

		_pool = engine.createPool({
			host: config.database.host,
			port: config.database.port,
			user: config.database.user,
			password: config.database.pass,
			database: config.database.name
			//debug: true
		});

		_pool.getConnection(callback);
	};

	this.closePrivateRoom = function(roomId){
		var time = parseInt(Date.getTimestamp()/1000);
		_pool.query('UPDATE chatroom_prooms SET closed = ? WHERE proom_id = ?',
			[time, roomId],
			function(err){
				if(err) throw err;
			}
		);
	};

	this.disconnectAllUsers = function(callback) {

		callback = callback || function(){};
		var time = parseInt(Date.getTimestamp()/1000);
		// Disconnect from private rooms
		_pool.query('UPDATE chatroom_psessions SET disconnected = ?',
			[time],
			function(err){
				if(err) throw err;
			}
		);

		_pool.query('UPDATE chatroom_prooms SET closed = ? WHERE closed = 0',
			[time],
			function(err){
				if(err) throw err;
			}
		);

		async.waterfall([
			function(next){
				_pool.query(
					'SELECT * FROM chatroom_users',
					function(err, result){
						if(err) throw err;

						next(err, result);
					}
				);
			},
			function(result, next)
			{
				async.eachSeries(result, function(element, nextLoop){
					_getNextId('chatroom_sessions', function(sessionId) {
						_pool.query('INSERT INTO chatroom_sessions SET ?',
							{
								sess_id: sessionId,
								room_id: element.room_id,
								user_id: element.user_id,
								userdata: element.userdata,
								connected: element.connected,
								disconnected: time
							},
							function(err){
								if(err) throw err;
								nextLoop();
							}
						);
					});
				},
				function(err){
					if(err) throw err;
					next();
				});
			},
			function(next) {
				// Disconnect from chat
				_pool.query('DELETE FROM chatroom_users',
					function(err){
						if(err) throw err;

						next();
					}
				);
			}
		],function(err){
			if(err) throw err;

			Container.getLogger().info('Successfully disconnected all users from server');
			callback();
		});
	};

	this.disconnectUser = function(subscriber, roomIds, subRoomIds) {
		var time = parseInt(Date.getTimestamp()/1000);

		// Disconnect from private rooms
		if(subRoomIds.length > 0 )
		{
			_pool.query('UPDATE chatroom_psessions SET disconnected = ? WHERE user_id = ? AND proom_id IN (?)',
					[time, subscriber.getId(), subRoomIds],
					function(err){
						if(err) throw err;
					}
			);
		}

		// Write chat_session

		if(roomIds.length > 0)
		{
			async.waterfall([
				function(next){
					_pool.query(
						'SELECT * FROM chatroom_users WHERE user_id = ? AND room_id IN (?)',
						[subscriber.getId(), roomIds],
						function(err, result){
							if(err) throw err;

							next(null, result);
						}
					);
				},
				function(result, next)
				{
					async.eachSeries(result, function(element, nextLoop){
						_getNextId('chatroom_sessions', function(sessionId) {
							_pool.query('INSERT INTO chatroom_sessions SET ?',
								{
									sess_id: sessionId,
									room_id: element.room_id,
									user_id: subscriber.getId(),
									userdata: element.userdata,
									connected: element.connected,
									disconnected: time
								},
								function(err){
									if(err) throw err;
									nextLoop();
								}
							);
						});
					},
					function(err){
						if(err) throw err;
						next();
					});
				},
				function(next) {
					_pool.query('DELETE FROM chatroom_users WHERE user_id = ? AND room_id IN (?)',
						[subscriber.getId(), roomIds],
						function(err){
							if(err) throw err;

							next();
						}
					);
				}
			],function(err){
				if(err) throw err;
			});
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
			}, function(err) {
				if(err) throw err;
			});
		})
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
		async.waterfall([
			function(next) {
				_pool.query('INSERT INTO '+tableName+'_seq (sequence) VALUES (NULL)', [], function(err, result){
					if(err) throw err;

					next(null, result.insertId);
				});
			},
			function(insertId, next) {
				_pool.query('DELETE FROM '+tableName+'_seq WHERE sequence < ?', [insertId], function(err) {
					if(err) throw err;

					next(null, insertId);
				});
			}
		], function(err, insertId) {
			if(err) throw err;

			callback(insertId);
		});
	}
};


module.exports = Database;