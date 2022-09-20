(function (root, scope, factory) {
	scope.OnScreenChatNotifications = factory(root, scope, root.jQuery);
}(window, il, function init(root, scope, $) {
	"use strict";

	let Logger = (function () {
		const defineLogLevel = function defineLogLevel(value, name) {
			return {
				value: value,
				name: name
			};
		};

		const invoke = function invoke(level, messageArguments) {
			if (messageArguments.length > 0) {
				let firstElement = messageArguments.shift();
				if (typeof firstElement === "string") {
					firstElement = "OSC Web Notifications | " + firstElement;

					if (0 === messageArguments.length) {
						messageArguments = firstElement
					} else {
						messageArguments.unshift(firstElement);
					}
				}
			}

			if (this.enabledFor(level)) {
				if (level.name.toLowerCase() in console) {
					console[level.name.toLowerCase()](messageArguments);
				} else {
					if (level.value > Logger.ERROR.value) {
						console.error(messageArguments);
					} else if (level.value > Logger.INFO.value) {
						console.info(messageArguments);
					}
				}
			}
		};

		class Logger {
			constructor(level) {
				this.setLevel(level);
			}

			setLevel(level) {
				if (level && "value" in level) {
					this.level = level;
				}
			}

			getLevel() {
				return this.level;
			}

			enabledFor(level) {
				return level.value >= this.level.value;
			}

			trace(...args) {
				invoke.call(this, Logger.TRACE, args);
			}

			debug(...args) {
				invoke.call(this, Logger.DEBUG, args);
			}

			notice(...args) {
				invoke.call(this, Logger.NOTICE, args);
			}

			info(...args) {
				invoke.call(this, Logger.INFO, args);
			}

			warn(...args) {
				invoke.call(this, Logger.WARNING, args);
			}

			error(...args) {
				invoke.call(this, Logger.ERROR, args);
			}

			critical(...args) {
				invoke.call(this, Logger.CRITICAL, args);
			}

			alert(...args) {
				invoke.call(this, Logger.ALERT, args);
			}

			emergency(...args) {
				invoke.call(this, Logger.EMERGENCY, args);
			}

			log(...args) {
				this.info(...args);
			}

			static levelForNumericValue(numericLevel) {
				if (!!isNaN(numericLevel)) {
					return Logger.DEBUG;
				}

				for (let level of Logger.LEVELS) {
					if (parseInt(numericLevel) === level.value) {
						return level;
					}
				}

				return Logger.DEBUG;
			}
		}

		Logger.TRACE = defineLogLevel(50, 'TRACE');
		Logger.DEBUG = defineLogLevel(100, 'DEBUG');
		Logger.INFO = defineLogLevel(200, 'INFO');
		Logger.NOTICE = defineLogLevel(250, 'NOTICE');
		Logger.WARNING = defineLogLevel(300, 'WARN');
		Logger.ERROR = defineLogLevel(400, 'ERROR');
		Logger.CRITICAL = defineLogLevel(500, 'CRITICAL');
		Logger.ALERT = defineLogLevel(550, 'ALERT');
		Logger.EMERGENCY = defineLogLevel(600, 'EMERGENCY');
		Logger.OFF = defineLogLevel(1000, 'OFF');

		Logger.LEVELS = [
			Logger.TRACE,
			Logger.DEBUG,
			Logger.INFO,
			Logger.NOTICE,
			Logger.WARNING,
			Logger.ERROR,
			Logger.CRITICAL,
			Logger.ALERT,
			Logger.EMERGENCY,
			Logger.OFF
		];

		return Logger;
	})();

	const NotificationStorage = (function() {
		let sentNotifications = {};

		const lsScope = "osc_webnoti_",
			tabNegotiationPrefix = "osc_webnotiat_",
			ignoreNotificationPrefix = "osc_webnotiig_";

		class NotificationStorage {
			/**
			 * 
			 */
			constructor() {
				this.gc();
			}

			/**
			 *
			 * @param {jQuery.Event} e
			 * @returns {boolean}
			 */
			shouldHandleEvent(e) {
				return (
					e.originalEvent !== undefined &&
					typeof e.originalEvent.key === 'string' &&
					e.originalEvent.key.indexOf(lsScope) !== -1
				);
			}

			/**
			 *
			 * @param {Object} notification
			 */
			markAsSent(notification) {
				sentNotifications[notification.uuid] = true
			}

			/**
			 *
			 * @param {Object} notification
			 * @returns {boolean}
			 */
			isMarkedAsSent(notification) {
				return sentNotifications.hasOwnProperty(notification.uuid);
			}

			/**
			 * 
			 * @param {Object} notification
			 */
			markAsIgnored(notification) {
				ls.setItem(ignoreNotificationPrefix + notification.uuid, "1");
			}

			/**
			 * 
			 * @param {Object} notification
			 * @returns {boolean}
			 */
			isIgnored(notification) {
				return ls.getItem(ignoreNotificationPrefix + notification.uuid) === "1";
			}

			/**
			 *
			 * @param {Object} notification
			 * @returns {(String|null)}
			 */
			getHandlingTab(notification)  {
				return ls.getItem(tabNegotiationPrefix + notification.uuid);
			}

			/**
			 *
			 * @param {Object} notification
			 * @returns {boolean}
			 */
			hasHandlingTab(notification)  {
				let handlingTab = ls.getItem(tabNegotiationPrefix + notification.uuid);

				return null !== handlingTab;
			}

			/**
			 *
			 * @param {Object} notification
			 * @param {String} tabId
			 */
			setHandlingTab(notification, tabId)  {
				ls.setItem(tabNegotiationPrefix + notification.uuid, tabId);
			}

			/**
			 * 
			 * @param {Object} notification
			 */
			emit(notification) {
				// Emit event to all other browser tabs
				ls.setItem(lsScope + notification.uuid, JSON.stringify(notification));

				// Emit event for the current tab
				let e = $.Event("storage");
				e.originalEvent = {
					key: lsScope + notification.uuid,
					oldValue: "oldValue",
					newValue: notification
				};
				$(root).trigger(e);
			}

			shouldTriggerForConversation(uuid) {
				if (scope.OnScreenChat.storage === undefined) {
					return true;
				}

				let conversation = scope.OnScreenChat.storage.get(uuid);
				if (null === conversation) {
					return false;
				}

				let now = new Date();

				if (conversation.lastTriggeredNotificationTs !== undefined) {
					let triggered = new Date(conversation.lastTriggeredNotificationTs);

					logger.info("Now: " + now.toLocaleTimeString());
					logger.info("Last Triggered: " + triggered.toLocaleTimeString());
					logger.info("Idle Time (Minutes): " + globalSettings.conversationIdleTimeThreshold);

					return (
						triggered.getTime() < now.getTime() - (globalSettings.conversationIdleTimeThreshold * 60 * 1000)
					);
				}

				logger.info("No timestamp registered for last sent notification");
				return true;
			}

			markTriggeredForConversation(uuid) {
				if (scope.OnScreenChat.storage === undefined) {
					return;
				}

				let conversation = scope.OnScreenChat.storage.get(uuid);

				if (null !== conversation) {
					conversation.lastTriggeredNotificationTs = (new Date()).getTime();
					scope.OnScreenChat.storage.save(conversation);
				}
			}

			/**
			 * 
			 */
			gc() {
				root.setInterval(function() {
					// https://caniuse.com/#feat=mdn-javascript_operators_destructuring_rest_in_objects
					//let items = {...ls};

					for (let [key, value] of Object.entries(ls)) {
						if (key.indexOf(lsScope) !== -1) {
							let notification;

							if (!ls.hasOwnProperty(key)) {
								continue;
							}

							notification = ls[key];
							if (typeof notification === "string") {
								notification = JSON.parse(notification);
							}

							if (notification.ts < (new Date()).getTime() - (60 * 1000)) {
								logger.debug("Garbage collected: " + notification.uuid);
								ls.removeItem(ignoreNotificationPrefix + notification.uuid);
								ls.removeItem(tabNegotiationPrefix + notification.uuid);
								ls.removeItem(key);
							}
						}
					}
				}, (60 * 1000));
			}
		}

		return NotificationStorage;
	})();

	let methods = {},
		logger = null,
		storage = null,
		globalSettings,
		defaults = {
			conversationIdleTimeThreshold: 1,
			logLevel: Logger.DEBUG
		},
		ls = "localStorage" in root ? root.localStorage : (function() {
			let items = {};

			return {
				removeItem: function (key) {
					if (items.hasOwnProperty(key)) {
						delete items.key;
					}
				},

				getItem: function (key) {
					if (items.hasOwnProperty(key)) {
						return items.key;
					}

					return null;
				},

				setItem: function (key, value) {
					items.key = value;
				}
			};
		})();

	/**
	 *
	 * @param {Object} notification
	 * @param respectIdleTime
	 */
	const delegateBrowserNotification = function delegateBrowserNotification(notification, respectIdleTime = false) {
		logger.debug("Entered final browser notification handling for message with id: " + notification.uuid);
		if (!storage.isMarkedAsSent(notification)) {
			if (il.BrowserNotifications.isSupported()) {
				storage.markAsSent(notification);

				if (respectIdleTime) {
					if (!storage.shouldTriggerForConversation(notification.conversationUuid)) {
						logger.info("Notification not triggered because idle time was not exceeded for conversation with id: " + notification.conversationUuid + " (message id: " + notification.uuid + ")");
						return;
					}

					storage.markTriggeredForConversation(notification.conversationUuid);
				}

				il.BrowserNotifications.requestPermission().then(() => {
					il.BrowserNotifications.notification(notification.title, {
						closeOnClick: true,
						tag: notification.uuid,
						body: notification.body,
						icon: notification.icon
					}).show();
					logger.info("Notification sent for message: " + notification.uuid);
				}).catch(() => {
					logger.error("Exception, permissions not granted");
				});
			} else {
				logger.error("Exception, Web Notifications not supported");
			}
		} else {
			logger.debug("Notification already sent for message: " + notification.uuid);
		}
	};

	/**
	 * 
	 * @param {Object} notification
	 */
	const tabNegotiationHandler = function tabNegotiationHandler(notification) {
		let tabId = Math.random() * 10000;

		logger.debug("Entered tab negotiation (tab id: " + tabId + ") for notification: " + notification.uuid);
		if (!storage.hasHandlingTab(notification)) {
			logger.info("Setting tab id to storage for notification: " + notification.uuid);
			storage.setHandlingTab(notification, tabId);
		} else {
			logger.debug("Another tab already set it's tab id to storage for notification: " + notification.uuid);
		}

		let handlingTab = storage.getHandlingTab(notification);
		if (handlingTab === tabId.toString()) {
			logger.debug("Tab negotiated, using browser API to send notification: " + notification.uuid);
			delegateBrowserNotification(notification);
		} else {
			logger.debug("Tab ignored, another tab (tab id: " + handlingTab + ") will send notification: " + notification.uuid);
		}
	};

	/**
	 * 
	 * @param {jQuery.Event} e
	 */
	const onWebNotificationBroadCast = function onWebNotificationBroadCast(e) {
		if (storage.shouldHandleEvent(e)) {
			let notification = e.originalEvent.newValue;

			if (null === notification) {
				return;
			}

			if (typeof notification === "string") {
				notification = JSON.parse(notification);
			}

			if (il.UICore.isPageVisible()) {
				storage.markAsIgnored(notification);
				logger.debug("Ignoring event because event receiving tab is visible: " + notification.uuid);
			} else if (storage.isIgnored(notification)) {
				logger.debug("Ignoring event because one tab marked notification as 'to be ignored': " + notification.uuid);
			} else {
				logger.debug("Tab is invisible, no other tab seems to be visible. Delegating event for: " + notification.uuid);
				tabNegotiationHandler(notification);
			}
		}
	};

	/**
	 *
	 * @param settings
	 */
	methods.init = function(settings) {
		globalSettings = $.extend({}, defaults, settings);

		logger = new Logger(Logger.levelForNumericValue(globalSettings.logLevel));

		if (!("localStorage" in root)) {
			logger.warn("No 'localStorage' support.");
		}

		storage = new NotificationStorage(ls);

		$(root).on("storage", onWebNotificationBroadCast);
	};

	/**
	 * 
	 * @param {string} uuid
	 * @param {string} conversationUuid
	 * @param {string} title
	 * @param {string} body
	 * @param {string} icon
	 */
	methods.send = function(uuid, conversationUuid, title, body, icon = "") {
		let notification = {
			uuid: uuid,
			conversationUuid: conversationUuid,
			title: title,
			body: body,
			icon: icon,
			ts: (new Date()).getTime()
		};

		logger.debug("Started browser notification handling for incoming chat message with id: " + notification.uuid);

		if (il.UICore.isPageVisible()) {
			logger.debug("Current tab is visible, directly show message. The user was able to notice the chat message: " + notification.uuid);
			delegateBrowserNotification(notification, true);
			storage.markAsIgnored(notification);
		} else {
			root.setTimeout(function() {
				logger.debug("Propagating event because current tab is hidden for chat message: " + notification.uuid);

				storage.emit(notification);
			}, 50);
		}
	};

	return methods;
}));