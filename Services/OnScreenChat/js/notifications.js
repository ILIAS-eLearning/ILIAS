(function (root, scope, factory) {
	scope.OnScreenChatNotifications = factory(root, root.jQuery);
}(window, il, function init(root, $) {
	"use strict";

	const lsScope = "osc_web_noti",
		tabNegotiationPrefix = "osc_at_",
		ignoreNotificationPrefix = "osc_ig_";

	let methods = {},
		ls = root.localStorage,
		sendNotifications = {};

	// TODO: Optimize storage/check of ignored events / Cleanup of LocalStorage

	/**
	 * 
	 * @param {Object} notification
	 */
	let delegateBrowserNotification = function delegateBrowserNotification(notification) {
		console.log("OSC Web Notifications| Finally handling notification for for message with ID: " + notification.uuid);
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
					console.log("OSC Web Notifications| Notification sent for message: " + notification.uuid);
				}).catch(() => {
					console.log("OSC Web Notifications| Exception, permissions not granted");
				});
			} else {
				console.log("OSC Web Notifications| Exception, Web Notifications not supported");
			}
		} else {
			console.log("OSC Web Notifications| Notification already sent for message: " + notification.uuid);
		}
	};

	/**
	 * 
	 * @param {Object} notification
	 */
	let tagNegotiationHandler = function tabNegotiationHandler(notification) {
		let tabId = Math.random() * 10000,
			activeTabIdentifier = tabNegotiationPrefix + notification.uuid;

		console.log("OSC Web Notifications| Entered tab negotiation (tab id: " + tabId + ") for notification: " + notification.uuid);
		if (null === ls.getItem(activeTabIdentifier)) {
			console.log("OSC Web Notifications| Setting tab id to storage for notification: " + notification.uuid);
			ls.setItem(activeTabIdentifier, tabId);
		} else {
			console.log("OSC Web Notifications| Another tab already set it's tab id to storage for notification: " + notification.uuid);
		}

		let handlingTab = ls.getItem(activeTabIdentifier);
		if (handlingTab === tabId.toString()) {
			console.log("OSC Web Notifications| Tab negotiated, using browser API to send notification: " + notification.uuid);
			delegateBrowserNotification(notification);
		} else {
			console.log("OSC Web Notifications| Tab ignored, another tab (tab id: " + handlingTab + ") will send notification: " + notification.uuid);
		}
	};
	
	let markNotificationAsIgnored = function markNotificationAsIgnored(uuid) {
		localStorage.setItem(ignoreNotificationPrefix + uuid, "1");
	};

	let isNotificationIgnored = function isNotificationIgnored(uuid) {
		return ls.getItem(ignoreNotificationPrefix + uuid) === "1";
	};

	/**
	 * 
	 * @param {jQuery.Event} e
	 */
	let onWebNotificationBroadCast = function onWebNotificationBroadCast(e) {
		if (e.originalEvent !== undefined && typeof e.originalEvent.key === 'string' && e.originalEvent.key === lsScope) {
			let notification = e.originalEvent.newValue;
			if (typeof notification === "string") {
				notification = JSON.parse(notification);
			}

			if (il.UICore.isPageVisibile()) {
				markNotificationAsIgnored(notification.uuid);
				console.log("OSC Web Notifications| Ignoring event because event receiving tab is visible: " + notification.uuid);
			} else if (isNotificationIgnored(notification.uuid)) {
				console.log("OSC Web Notifications| Ignoring event because one tab marked notification as 'to be ignored': " + notification.uuid);
			} else {
				console.log("OSC Web Notifications| Tab is invisible, no other tab seems to be visible. Delegating event for: " + notification.uuid);
				tagNegotiationHandler(notification);
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

		if (il.UICore.isPageVisibile()) {
			console.log("OSC Web Notifications| Current tab is visible, ignoring message. The user was able to notice the chat message: " + notification.uuid);
			markNotificationAsIgnored(notification.uuid);
		} else {
			root.setTimeout(function() {
				console.log("OSC Web Notifications| Propagating event because current tab is hidden for chat message: " + notification.uuid);

				// Emit event to all other browser tabs
				ls.setItem(lsScope, JSON.stringify(notification));

				// Emit event for the current tab
				let e = $.Event("storage");
				e.originalEvent = {
					key: lsScope,
					oldValue: "oldValue",
					newValue: notification
				};
				$(root).trigger(e);
			}, 50);
		}
	};

	return methods;
}));