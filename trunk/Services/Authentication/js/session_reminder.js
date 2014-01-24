(function($) {

	$.fn.ilSessionReminder = function(method) {

		var session_reminder_executer = null;
		
		var session_reminder_locked = false;

		var PeriodicalExecuter = function(callback, frequency) {
			this.callback = callback;
			this.frequency = frequency;
			this.currentlyExecuting = false;
			this.registerCallback();
		};

		PeriodicalExecuter.prototype = {
			registerCallback:function () {
				var me = this;
				this.timer = setInterval(function () {
					me.onTimerEvent()
				}, this.frequency);
			},

			execute:function () {
				this.callback(this);
			},

			stop:function () {
				if (!this.timer) return;
				clearInterval(this.timer);
				this.timer = null;
			},

			onTimerEvent:function () {
				if (!this.currentlyExecuting) {
					try {
						this.currentlyExecuting = true;
						this.execute();
					} finally {
						this.currentlyExecuting = false;
					}
				}
			}
		};
		
		var internals = {
			log: function(message) {
				if (this.properties.debug) {
					console.log(message);
				}
			},
			properties: {
			}
		};

		var ilSessionReminderCallback = function() {
			var properties = internals.properties;
			var cookie_prefix = "il_sr_" + properties.client_id + "_";
			
			if (YAHOO.util.Cookie.get(cookie_prefix + "activation") == "disabled" ||
				YAHOO.util.Cookie.get(cookie_prefix + "status") == "locked") {
				internals.log("Session reminder disabled or locked for current user session");
				return;
			}

			YAHOO.util.Cookie.set(cookie_prefix + "status", "locked");
			session_reminder_locked = true;
			internals.log("Session reminder locked");
			$.ajax({
				url:     properties.url,
				dataType:'json',
				type:    'POST',
				data: {
					session_id: properties.session_id
				},
				success: function (response) {
					if (response.message && typeof response.message == "string") {
						internals.log(response.message);
					}

					if (response.remind) {
						session_reminder_executer.stop();
						var extend = confirm(unescape(response.txt));
						if (extend == true) {
							$.ajax({
								url:     response.extend_url,
								type:    'GET',
								success: function () {
									session_reminder_executer = new PeriodicalExecuter(ilSessionReminderCallback, properties.frequency * 1000);
									YAHOO.util.Cookie.set(cookie_prefix + "status", "unlocked");
									session_reminder_locked = false;
									internals.log("User extends session: Session reminder unlocked");
								}
							}).fail(function() {
								session_reminder_executer = new PeriodicalExecuter(ilSessionReminderCallback, properties.frequency * 1000);
								YAHOO.util.Cookie.set(cookie_prefix + "status", "unlocked");
								session_reminder_locked = false;
								internals.log("XHR Failure: Session reminder unlocked");
							});
						} else {
							YAHOO.util.Cookie.set(cookie_prefix + "activation", "disabled");
							YAHOO.util.Cookie.set(cookie_prefix + "status", "unlocked");
							session_reminder_locked = false;
							internals.log("User disabled reminder for current session: Session reminder disabled but unlocked");
							session_reminder_executer = new PeriodicalExecuter(ilSessionReminderCallback, properties.frequency * 1000);
						}
					} else {
						YAHOO.util.Cookie.set(cookie_prefix + "status", "unlocked");
						session_reminder_locked = false;
						internals.log("Reminder of session expiration not necessary: Session reminder unlocked");
					}
				}
			}).fail(function() {
				YAHOO.util.Cookie.set(cookie_prefix + "status", "unlocked");
				session_reminder_locked = false;
				internals.log("XHR Failure: Session reminder unlocked");
			});
		};

		var methods = {
			init: function(params) {
				return this.each(function () {
					var $this = $(this);

					if ($this.data('sessionreminder')) {
						return;
					}

					var data = {
						properties: $.extend(
							true, {},
							{
								url         :"",
								client_id   :"",
								session_name:"",
								session_id  :"",
								frequency   :60,
								debug       :0
							},
							params
						)
					};

					$this.data("sessionreminder", data);
					internals.properties = data.properties;
					
					var properties = internals.properties;
					var cookie_prefix = "il_sr_" + properties.client_id + "_";

					$(window).unload(function() {
						if (session_reminder_locked) {
							YAHOO.util.Cookie.set(cookie_prefix + "status", "unlocked");
							internals.log("Unlocked session reminder on unload event");
						}
					});

					internals.log("Session reminder started");
					if (YAHOO.util.Cookie.get(cookie_prefix + "session_id") != YAHOO.util.Cookie.get(properties.session_name)) {
						YAHOO.util.Cookie.set(cookie_prefix + "activation", "enabled");
						YAHOO.util.Cookie.set(cookie_prefix + "status", "unlocked");
						YAHOO.util.Cookie.set(cookie_prefix + "session_id", YAHOO.util.Cookie.get(properties.session_name));
						internals.log("Cookied changed after new login or session reminder initially started for current session: Release lock and enabled reminder");
					}

					session_reminder_executer = new PeriodicalExecuter(
						ilSessionReminderCallback, properties.frequency * 1000
					);
				});
			}
		};

		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === "object" || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error("Method " + method + " does not exist on jQuery.ilSessionReminder");
		}

	};

})(jQuery);