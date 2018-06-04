(function (root, scope, factory) {
	scope.Alert = factory(root, root.jQuery);
}(window, il, function init(root, $) {
	"use strict";

	const FAILURE = "failure",
		  INFO = "info",
		  SUCCESS = "success",
		  QUESTION = "question";

	/**
	 * 
	 * @type {{templates: {failure: string, info: string, success: string, question: string}, placeholder: string}}
	 */
	const defaults = {
		templates: {
			failure: "",
			info: "",
			success: "",
			question: ""
		},
		placeholder: ""
	};

	/**
	 * 
	 * @type {{txt: string, type: string, autoHide: boolean, visibleForSeconds: number}}
	 */
	const messageDefaults = {
		txt: "",
		type: "",
		autoHide: false,
		visibleForSeconds: 5
	};

	/**
	 * 
	 * @return {string}
	 */
	function uuidv4() {
		return "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(/[xy]/g, (c) => {
			let r = Math.random() * 16 | 0,
				v = c === "x" ? r : (r & 0x3 | 0x8);

			return v.toString(16);
		});
	}

	/**
	 * 
	 * @param promises
	 * @return {Promise}
	 */
	function PromiseAll(promises) {
		let dfd = $.Deferred(),
			fulfilled = 0,
			length = promises.length,
			results = [];

		if (length === 0) {
			dfd.resolve(results);
		} else {
			promises.forEach((promise, i) => {
				$.when(promise)
					.then((value) => {
						results[i] = value;

						fulfilled++;
						if (fulfilled === length){
							dfd.resolve(results);
						}
					});
			});
		}

		return dfd.promise();
	}

	/**
	 * 
	 * @param jqueryMessageElement
	 * @return {Promise}
	 */
	function addAsGlobalAlert(jqueryMessageElement) {
		let container = $('.ilAdminRow');

		if (0 === container.size()) {
			container = $('<div class="ilAdminRow"></div>').append(jqueryMessageElement);

			container.insertBefore($("#ilContentContainer")).promise();
		} else {
			return container.append(jqueryMessageElement).promise();
		}
	}

	/**
	 * 
	 * @param options
	 * @return {{show: (function(*=): *), hide: (function(): *), getId: (function(): string), getType: (function(): *)}}
	 */
	function message(options) {
		let props = $.extend({}, messageDefaults, options);

		if (!globalSettings.templates.hasOwnProperty(props.type)) {
			throw new Error(
				`The 'type' option must one of '${FAILURE}', '${INFO}', '${SUCCESS}', '${QUESTION}'.`
			);
		}

		if (props.txt === undefined || typeof props.txt !== "string" || props.txt.length === 0) {
			throw new Error(
				"The 'txt' options must be a non empty string."
			);
		}

		let uniqueId = uuidv4(),
			hidden = true,
			template = $(globalSettings.templates[props.type]
				.replace(globalSettings.placeholder, props.txt));

		template.attr("id", uniqueId);
		template.hide();

		let pub = {
			/**
			 *
			 * @param insertToDomCallback
			 * @return {Promise}
			 */
			show: function (insertToDomCallback) {
				let dfd = new $.Deferred();

				if (!this.isHidden()) {
					dfd.resolve();
				} else {
					hidden = false;

					$.when((() => {
						let innerDeferred = jQuery.Deferred();

						if ($.isFunction(insertToDomCallback)) {
							$.when(insertToDomCallback(template))
								.then(() => {
									innerDeferred.resolve();
								});
						} else {
							$.when(addAsGlobalAlert(template))
								.then(() => {
									innerDeferred.resolve();

									$("html, body").animate({
										scrollTop: template.offset().top
									}, 500);
								});
						}

						return innerDeferred.promise();
					})())
						.then(() => {
							return (template
								.slideDown(500)
								.promise()
							);
						})
						.then(() => {
							if (props.autoHide) {
								setTimeout(() => {
									pub.hide();
								}, parseInt(props.visibleForSeconds, 10) * 1000);
							}

							dfd.resolve();
						});
				}

				return dfd.promise();
			},

			/**
			 *
			 * @return {Promise}
			 */
			hide: function () {
				let dfd = new $.Deferred();


				if (this.isHidden()) {
					dfd.resolve();
				} else {
					hidden = true;

					template
						.slideUp(500)
						.promise()
						.then(() => dfd.resolve());
				}

				return dfd.promise();
			},

			/**
			 *
			 * @return {string}
			 */
			getId: function () {
				return uniqueId;
			},

			/**
			 *
			 * @return {string}
			 */
			getType: function () {
				return props.type;
			},

			/**
			 *
			 * @return {boolean}
			 */
			isHidden: function () {
				return hidden;
			},

			/**
			 *
			 * @return {Promise}
			 */
			destroy: function () {
				let dfd = new $.Deferred();

				$.when(this.hide())
					.then(function() {
						return (
							template
								.remove()
								.promise()
						);
					})
					.then(function() {
						if (messages.hasOwnProperty(uniqueId)) {
							delete messages.uniqueId;
						}

						dfd.resolve();
					});

				return dfd.promise();
			}
		};

		messages[uniqueId] = pub;

		return pub;
	}

	/**
	 * 
	 * @param args
	 * @param {string} type
	 * @return {*}
	 */
	function parseMessageArgs (args, type) {
		let props = $.extend({}, messageDefaults, {
			type: type
		});

		if (args.length === 1 && typeof args[0] === "object") {
			props = $.extend(props, args[0], {
				type: type
			});
		} else {
			if (args.length > 0 && typeof args[0] === "string") {
				props.txt = args[0];
			}

			if (args.length > 1 && typeof args[1] === "number") {
				props.visibleForSeconds = args[1];
				props.autoHide          = true;
			}
		}

		return props;
	}

	let globalSettings = defaults,
		methods = {},
		messages = {};

	/**
	 * 
	 * @param settings
	 */
	methods.init = function (settings) {
		globalSettings = $.extend({}, defaults, settings);
	};

	/**
	 * 
	 * @return {{show: (function(*=): *), hide: (function(): *), getId: (function(): string), getType: (function(): *)}}
	 */
	methods.failure = function () {
		const args = arguments;

		return message(parseMessageArgs(args, FAILURE));
	};

	/**
	 * 
	 * @return {{show: (function(*=): *), hide: (function(): *), getId: (function(): string), getType: (function(): *)}}
	 */
	methods.info = function () {
		const args = arguments;

		return message(parseMessageArgs(args, INFO));
	};

	/**
	 * 
	 * @return {{show: (function(*=): *), hide: (function(): *), getId: (function(): string), getType: (function(): *)}}
	 */
	methods.success = function () {
		const args = arguments;

		return message(parseMessageArgs(args, SUCCESS));
	};

	/**
	 * 
	 * @return {{show: (function(*=): *), hide: (function(): *), getId: (function(): string), getType: (function(): *)}}
	 */
	methods.question = function () {
		const args = arguments;

		return message(parseMessageArgs(args, QUESTION));
	};

	/**
	 * 
	 * @return {Promise}
	 */
	methods.hide = function() {
		const args = arguments;

		let dfd = new $.Deferred();

		setTimeout(() => {
			let foundMessages = (function() {
				let msgs = Object
					.keys(messages)
					.map((key) => messages[key]);

				if (args.length === 1 && typeof args[0] === "string" && globalSettings.templates.hasOwnProperty(args[0])) {
					return msgs.filter((m) => (m.getType() === args[0]));
				} else {
					return msgs.filter((m) => m);
				}
			}());

			let hideActions = foundMessages.map((m) => m.destroy());

			PromiseAll(hideActions).then(() => {
				dfd.resolve();
			});

		}, 0);

		return dfd.promise();
	};

	return methods;
}));