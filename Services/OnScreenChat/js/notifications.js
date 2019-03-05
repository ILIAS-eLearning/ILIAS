(function (root, scope, factory) {
	scope.OnScreenChatNotifications = factory(root, root.jQuery);
}(window, il, function init(root, $) {
	"use strict";

	const lsScope = "osc_web_noti_",
		tabNegotiationPrefix = "osc_at_",
		ignoreNotificationPrefix = "osc_ig_";

	let methods = {},
		ls = root.localStorage,
		sendNotifications = {};

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

			if (this.enabledFor(level) && level.name.toLowerCase() in console) {
				console[level.name.toLowerCase()](messageArguments);
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

			error(...args) {
				invoke.call(this, Logger.ERROR, args);
			}

			info(...args) {
				invoke.call(this, Logger.INFO, args);
			}

			trace(...args) {
				invoke.call(this, Logger.TRACE, args);
			}

			debug(...args) {
				invoke.call(this, Logger.DEBUG, args);
			}

			warn(...args) {
				invoke.call(this, Logger.WARN, args);
			}
			
			log(...args) {
				this.info(args);
			}
		}

		Logger.TRACE = defineLogLevel(1, 'TRACE');
		Logger.DEBUG = defineLogLevel(2, 'DEBUG');
		Logger.DEBUG = defineLogLevel(3, 'INFO');
		Logger.WARN = defineLogLevel(4, 'WARN');
		Logger.ERROR = defineLogLevel(5, 'ERROR');

		return Logger;
	})();

	let logger = new Logger(Logger.DEBUG);

	let markNotificationAsIgnored = function markNotificationAsIgnored(uuid) {
		localStorage.setItem(ignoreNotificationPrefix + uuid, "1");
	};

	let isNotificationIgnored = function isNotificationIgnored(uuid) {
		return ls.getItem(ignoreNotificationPrefix + uuid) === "1";
	};

	/**
	 * 
	 * @param {Object} notification
	 */
	let delegateBrowserNotification = function delegateBrowserNotification(notification) {
		logger.debug("Entered final browser notification handling for message with id: " + notification.uuid);
		if (!sendNotifications.hasOwnProperty(notification.uuid)) {
			if (il.BrowserNotifications.isSupported()) {
				sendNotifications[notification.uuid] = true;
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

		ls.removeItem(lsScope + notification.uuid);
	};

	/**
	 * 
	 * @param {Object} notification
	 */
	let tabNegotiationHandler = function tabNegotiationHandler(notification) {
		let tabId = Math.random() * 10000,
			activeTabIdentifier = tabNegotiationPrefix + notification.uuid;

		logger.debug("Entered tab negotiation (tab id: " + tabId + ") for notification: " + notification.uuid);
		if (null === ls.getItem(activeTabIdentifier)) {
			logger.info("Setting tab id to storage for notification: " + notification.uuid);
			ls.setItem(activeTabIdentifier, tabId);
		} else {
			logger.debug("Another tab already set it's tab id to storage for notification: " + notification.uuid);
		}

		let handlingTab = ls.getItem(activeTabIdentifier);
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
	let onWebNotificationBroadCast = function onWebNotificationBroadCast(e) {
		if (e.originalEvent !== undefined && typeof e.originalEvent.key === 'string' && e.originalEvent.key.indexOf(lsScope) !== -1) {
			let notification = e.originalEvent.newValue;
			if (typeof notification === "string") {
				notification = JSON.parse(notification);
			}

			if (il.UICore.isPageVisible()) {
				markNotificationAsIgnored(notification.uuid);
				logger.debug("Ignoring event because event receiving tab is visible: " + notification.uuid);
			} else if (isNotificationIgnored(notification.uuid)) {
				logger.debug("Ignoring event because one tab marked notification as 'to be ignored': " + notification.uuid);
			} else {
				logger.debug("Tab is invisible, no other tab seems to be visible. Delegating event for: " + notification.uuid);
				tabNegotiationHandler(notification);
			}
		}
	};

	// Register listener for storage events
	$(root).on("storage", onWebNotificationBroadCast);

	/**
	 * 
	 * @param {string} uuid
	 * @param {string} title
	 * @param {string} body
	 * @param {string} icon
	 */
	methods.send = function(uuid, title, body, icon = "") {
		let notification = {
			uuid: uuid,
			title: title,
			body: body,
			icon: icon
		};

		logger.debug("Started browser notification handling for incoming chat message with id: " + notification.uuid);

		if (il.UICore.isPageVisible()) {
			logger.debug("Current tab is visible, ignoring message. The user was able to notice the chat message: " + notification.uuid);
			markNotificationAsIgnored(notification.uuid);
		} else {
			root.setTimeout(function() {
				logger.debug("Propagating event because current tab is hidden for chat message: " + notification.uuid);

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
			}, 50);
		}
	};

	return methods;
}));