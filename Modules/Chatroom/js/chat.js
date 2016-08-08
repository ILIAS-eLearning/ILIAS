(function($) {

	$(document).click(function() {
		$('.dropdown-menu.menu').hide();
	});
	
	var iconsByType = {
		user: 'templates/default/images/icon_usr.svg',
		room: 'templates/default/images/icon_chtr.svg'
	};

	var inArray = function(ar, value) {
		if (ar && (typeof ar == 'object' || typeof ar == 'array')) {
			for(var i in ar) {
				if (ar[i] == value) {
					return true;
				}
			}
		}
		return false;
	};

	$.fn.ilChatDialog = function (method) {
		var methods = {
			init: function (params) {
				var $content = $(this);

				var defaultButtons = (params.defaultButtons === false) ? [] : [
					{
						id:      "ok",
						label:   translate("ok"),
						callback:function (e) {
							var close = true;
							if (typeof params.positiveAction === "function") {
								close = params.positiveAction.call($content, e);
							}
							if (typeof close === "undefined" || close) {
								$content.ilChatDialog("close");
							}
						}
					},
					{
						id:      "cancel",
						label:   translate("cancel"),
						callback:function (e) {
							var close = true;
							if (typeof params.negativeAction == "function") {
								close = params.negativeAction.call($content, e);
							}
							if (typeof close === "undefined" || close) {
								$content.ilChatDialog("close");
							}
						}
					}
				];

				var properties = $.extend(true, {}, {
					title:           '',
					buttons:         defaultButtons,
					disabled_buttons:[]
				}, params);

				var dialogBody = $('<div class="ilChatDialogBody">'),
					buttons = {};

				if (properties.buttons) {
					$.each(properties.buttons, function () {
						var btn = this;

						// IE: properties.disabled_buttons is of type object instead of array

						if (btn.id && inArray(properties.disabled_buttons, btn.id)) {
							return;
						}

						buttons[btn.id] = {
							type:      "button",
							label:     this.label,
							className: "btn btn-default",
							callback:  function (e, $modal) {
								if (typeof btn.callback === "function") {
									btn.callback();
								}
							}
						};
					});
				}

				$(this).appendTo(dialogBody).show();

				var $modal = il.Modal.dialogue({
					show: true,
					header: properties.title || null,
					body: $(this),
					buttons: buttons || []
				});

				$(this).data("ilChatDialog", $.extend(properties, {
					_modal: $modal,
					_parent: $(this).parent()
				}));

				return $(this);
			},
			close: function () {
				var data = $(this).data("ilChatDialog");

				if (typeof data.close === "function") {
					data.close();
				}

				data._modal.hide();
			}
		};

		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('Method ' + method + ' does not exist on jQuery.ilChatDialog');
		}
	};

	function getMenuLine(label, callback, icon) {
		var line = $('<li></li>');
		var content = $('<a class="" href="#">'+(icon ? ('<img style="margin-right: 8px" src="'+icon+'"/>'): '')+'<span class="xsmall">'+label+'</span></a>');
		line.append(content);
		if (callback) {
			line.bind('click', function(ev) {
				$(this).parents('.menu').hide();
				callback.call($(this).parents('.menu'));
				ev.preventDefault();
				ev.stopPropagation();
				return false;
			});
		}
		return line;
	}

	var menuContainer;

	$.fn.ilChatMenu = function( method ) {
	
		var methods = {
			init: function(menuitems) {

			},
			show: function(menuitems, alignToRight) {

				if (!menuContainer) {
					menuContainer = $('<ul class="dropdown-menu menu" role="menu"></ul>')
						.appendTo($(this));
				}
				
				if (alignToRight != undefined && alignToRight) {
					menuContainer.addClass("pull-right");
				} else {
					menuContainer.removeClass("pull-right");
				}

				menuContainer.find('li').remove();

				var table = menuContainer;

				$.each(menuitems, function() {
					var line = getMenuLine(this.separator ? '<hr/>' : this.label, this.callback, this.icon).appendTo(table);
					if (this.addClass) {
						line.find('span').addClass(this.addClass);
					}
				});

				menuContainer.data('ilChatMenu', {
					_attatched: this
				});

				menuContainer.show();
			}
		};

		if ( methods[method] ) {
			return methods[method].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
			return methods.init.apply( this, arguments );
		} else {
			$.error( 'Method ' +  method + ' does not exist on jQuery.ilChatList' );
		}
	};

	$.fn.ilChatList = function( method ) {

		function getMenuLine(label, callback) {
			var line = $('<li></li>')
			.append(
				$('<a href="#"><span class="small">'+label+'</span></a>')
				.click(function(e) {
					$(this).parents('.menu').hide();
					e.stopPropagation();
					e.preventDefault();
					callback.call($(this).parents('.menu').data('ilChat').context);
				})
				);

			return line;
		}

		var menuContainer = $('<ul class="dropdown-menu menu" role="menu"></ul>')
			.appendTo($('body'));

		var methods = {
			init: function(menuitems) {
				$(this).data('ilChatList', {
					_index: {},
					_menuitems: menuitems
				});
			},
			add: function(options) {
				if ($(this).data('ilChatList')._index['id_' + options.id]) {
					if(options.label != undefined)
					{
						$(this).data('ilChatList')._index['id_' + options.id].find('.label').html(options.label);
					}
					return $(this);
				}

				var line = $(   
					'<div class="listentry '+options.type+'_'+options.id+' online_user"><img src="'+iconsByType[options.type]+'" />&nbsp;' +
						'<div class="btn-group">' +
							'<button class="btn btn-default dropdown-toggle" data-toggle="dropdown" data-container="body">' +
							'<span class="label">' + options.label + '</span> ' +
							'<span class="caret"></span>' +
							'</button>' +
						'</div>' +
					'</div>');

				if (typeof options.hide != 'undefined' && options.hide == true) {
					line.addClass('hidden_entry');
				} 

				line.data('ilChatList', options);

				var $this = $(this);
				$this.data('ilChatList')._index['id_' + options.id] = line;

				line.find("button").on("click", function(e) {

					e.preventDefault();
					e.stopPropagation();

					$("#chat_users .menu").hide();

					menuContainer.find('li').remove();

					var data = line.data('ilChatList');
					if (data.type == 'user' && data.id == personalUserInfo.userid) {
						return;
					}

					$.each($this.data('ilChatList')._menuitems, function() {

						if (this.permission == undefined) {
							menuContainer.append(getMenuLine(this.label, this.callback));
						}
						else if (
							//(personalUserInfo.moderator && this.permission.indexOf('moderator') >= 0)
							//|| (personalUserInfo.userid == data.owner && this.permission.indexOf('owner') >= 0)
							(personalUserInfo.moderator && inArray(this.permission, 'moderator') >= 0)
							|| (personalUserInfo.userid == data.owner && inArray(this.permission, 'owner') >= 0)
							) {
							menuContainer.append(getMenuLine(this.label, this.callback));
						}
					});

					menuContainer.appendTo(line.find(".btn-group"));

					menuContainer.data('ilChat', {
						context: line.data('ilChatList')
					});

					menuContainer.show();
				});

				if (options.type == 'user' && personalUserInfo.userid == options.id) {
					line.addClass('self');
				}
				else if (options.type == 'user') {
					// Deleted line according to mantis: #14831
				}
				else if (options.type == 'room' && options.owner == personalUserInfo.userid) {
					line.addClass('self');
				}

				$(this).append(line);
				if (line.hasClass('hidden_entry')) {
					line.hide();
				}
				
				if (options.type == 'user') {
					if ($('.online_user:visible').length == 0) {
						$('.no_users').show();
					}
					else {
						$('.no_users').hide();
					}
				}
				
				return $(this).ilChatList('sort');
			},
			sort: function() {
				var tmp = [];
				$.each($(this).data('ilChatList')._index, function(i) {
					tmp.push({id: i, data: this});
				});
				
				tmp.sort(function(a, b) {
					return (a.data.data('ilChatList').label < b.data.data('ilChatList').label) ? -1 : 1;
				});
				for(var i = 0; i < tmp.length; ++i) {
					$(this).append(tmp[i].data);
				}

				return $(this);
			},
			removeById: function(id) {
				var line = $(this).data('ilChatList')._index['id_' + id];
				if (line) {
					var data = line.data('ilChatList');
					//line.remove();
					if (data.type == 'user' || data.type == '') {
						$(data.type + '_' + id).remove();
						if (data.type == 'user') {
							if ($('.online_user:visible').length == 0) {
								$('.no_users').show();
							}
							else {
								$('.no_users').hide();
							}
						}
					}
					delete $(this).data('ilChatList')._index['id_' + id];
				}
				return $(this);
			},
			getDataById: function(id) {
				return $(this).data('ilChatList')._index['id_' + id] ? $(this).data('ilChatList')._index['id_' + id].data('ilChatList') : undefined;
			},
			setNewEvents: function(id, newEvents) {

				var data = $(this).data('ilChatList')._index['id_' + id].data('ilChatList');
				if(data)
				{
					data.new_events = newEvents;
				}
			},
			getAll: function() {
				var result = [];
				$.each($(this).data('ilChatList')._index, function() {
					result.push(this.data('ilChatList'));
				});

				result.sort(function(a, b) {
					return (a.label < b.label) ? -1 : 1;
				});

				return result;
			},
			clear: function() {
				$('#chat_users').find('div').not('.no_users').remove();
				menuContainer.html('');
				$(this).data('ilChatList', {
					_index: {},
					_menuitems: $(this).data('ilChatList')._menuitems
				});
			}
		};
	
		if ( methods[method] ) {
			return methods[method].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
			return methods.init.apply( this, arguments );
		} else {
			$.error( 'Method ' +  method + ' does not exist on jQuery.ilChatList' );
		}
  
	};

	var lastHandledDate = new Object();
	$.fn.ilChatMessageArea = function( method ) {
    
		var methods = {
			init: function() {
				$(this).data('ilChatMessageArea', {
					_scopes: {}
				});
			},
			addScope: function(scope_id, scope) {
				var tmp = $('<div class="messageContainer">');
				$(this).data('ilChatMessageArea')._scopes['id_' + scope_id] = tmp;
				//console.log($(this).data('ilChatMessageArea')._scopes);
				$(this).append(tmp);
				tmp.data('ilChatMessageArea', scope);
				tmp.hide();
			},
			addMessage: function(scope, message) {
				var containers;
				if (scope == -1) {
					containers = $(this).data('ilChatMessageArea')._scopes;
				}
				else {
					containers = [$(this).data('ilChatMessageArea')._scopes['id_' + scope]];
				}

				$.each(containers, function() {
					var container = this;

					if (!container || container == window) {
						return;
					}

                    var line = $('<div class="messageLine chat"></div>')
							.addClass((message.target != undefined && !message.target.public) ? 'private' : 'public');

					/*if (message.message && message.message.content) {
					    message = message.message;
					}*/

					switch(message.type) {
						case 'message':
							var content = message.content;
							/*var content;
							try {
							    content = JSON.parse(message.message);
								console.log(content);
							}
							catch (ex) {
							    console.log(ex);
							    return true;
							}*/



							
							var messageDate =  new Date(message.timestamp);

							if (typeof lastHandledDate.scope == "undefined" ||
								lastHandledDate.scope== null || 
								lastHandledDate.scope.getDate() != messageDate.getDate() ||
								lastHandledDate.scope.getMonth() != messageDate.getMonth() ||
								lastHandledDate.scope.getFullYear() != messageDate.getFullYear()) {
								container.append($('<div class="messageLine chat dateline"><span class="chat content date">' + formatISODate(message.timestamp) + '</span><span class="chat content username"></span><span class="chat content message"></span></div>'));
							}
							lastHandledDate.scope = messageDate;
							
							line.append($('<span class="chat content date"></span>').append('' + formatISOTime(message.timestamp) + ', '))
								.append($('<span class="chat content username"></span>').append(message.from.username));

							if (message.target) {
								if (message.target.username != "") {
									line.append($('<span class="chat recipient">@</span>').append(message.target.username))
								}
								else {
									line.append($('<span class="chat recipient">@</span>').append('unkown'))
								}
							}
							/*if (message.recipients) {
								var parts = message.recipients.split(',');
								for (var i in parts) {
									if (parts[i] != message.username.id) {
										var data = $('#chat_users').ilChatList('getDataById', parts[i]);
										if (data) {
											line.append($('<span class="chat recipient">@</span>').append(data.label))
										}
										else {
											line.append($('<span class="chat recipient">@</span>').append('unkown'))
										}
									}
								}
							}*/

							var messageSpan = $('<span class="chat content message"></span>');
								messageSpan.text(messageSpan.text(content).text())
									.html(smileys.replace(messageSpan.text()));
							line.append($('<span class="chat content messageseparator">:</span>'))
								.append(messageSpan);

							for(var i in message.format) {
								if (i != 'color')
									messageSpan.addClass( i + '_' + message.format[i]);
							}

							messageSpan.css('color', message.format.color);

							if (message.subRoomId != subRoomId) {
								$('.room_' + message.subRoomId).addClass('new_events');
							}

							break;
						case 'connected':
							if (message.login || (message.users[0] && message.users[0].login)) {
							    /*line
							    .append($('<span class="chat content date"></span>').append('(' + formatISOTime(message.timestamp || message.message.timestamp) + ') '))
							    .append($('<span class="chat content username"></span>').append(message.login || message.message.users[0].login))
							    .append($('<span class="chat content messageseparator">:</span>'))
							    .append($('<span class="chat content message"></span>').append(translate('connect')));*/
								line
								    .append($('<span class="chat"></span>').append(translate('connect', {username: message.users[0].login})));
								line.addClass('notice');
							}
							break;
						case 'disconnected':
							if (message.login || (message.users[0] && message.users[0].login)) {
							    /*line
							    .append($('<span class="chat content date"></span>').append('(' + formatISOTime(message.timestamp || message.message.timestamp) + ') '))
							    .append($('<span class="chat content username"></span>').append(message.login || message.message.users[0].login))
							    .append($('<span class="chat content messageseparator">:</span>'))
							    .append($('<span class="chat content message"></span>').append(translate('disconnected')));*/
								line
								    .append($('<span class="chat"></span>').append(translate('disconnected', {username: message.users[0].login})));
								line.addClass('notice');
							}
							break;
						case 'private_room_entered':
							if (message.login || (message.users[0] && message.users[0].login)) {
							    line
							    .append($('<span class="chat content date"></span>').append('' + formatISOTime(message.timestamp) + ', '))
							    .append($('<span class="chat content username"></span>').append(message.login || message.users[0].login))
							    .append($('<span class="chat content messageseparator">:</span>'))
							    .append($('<span class="chat content message"></span>').append(translate('connect', {username: message.users[0].login})));
							}
							break;
						case 'private_room_left':
						case 'notice':
							line
							    .append($('<span class="chat"></span>').append(message.content));
							line.addClass('notice');
							break;
						case 'error':
							line
							.append($('<span class="chat"></span>').append(message.content));
							line.addClass('error');
							break;
						case 'userjustkicked':
							break;
					}

					container.append(line);

					if(message.subRoomId == subRoomId)
					{
						scrollChatArea(container);
					}
				});

                    
				return $(this);
			},
			hasContent: function(id) {
				return $(this).data('ilChatMessageArea')._scopes['id_' + id].find('div').length > 0;
			},
			clearMessages: function(id) {
				$(this).data('ilChatMessageArea')._scopes['id_' + id].find('div').html('');
			},
			show: function(id, posturl, leaveCallback) {
				var scopes = $(this).data('ilChatMessageArea')._scopes;
                    
				$.each(scopes, function() {
					$(this).hide();
				});
                    
				scopes['id_' + id].show();
				scrollChatArea(scopes['id_' + id]);
				if (id == 0) {
				    $('.current_room_title').text(scopes['id_' + id].data('ilChatMessageArea').title);
				}
				else {
				    $('.current_room_title').html('').append(
					$('<a href="#"></a>')
					    .text(translate('main'))
					    .click(function(e) {
							e.preventDefault();
							e.stopPropagation();
							iliasConnector.leavePrivateRoom(currentRoom);
							currentRoom = 0;
					    })
				    )
				    .append('&nbsp;&rarr;&nbsp;' + scopes['id_' + id].data('ilChatMessageArea').title);
				}
                    
				$('.in_room').removeClass('in_room');
                    
				$('.room_' + id).addClass('in_room');

				if (!id) {
					$('#chat_users').find('.online_user').not('.hidden_entry').show();
				}
				else {
					$('#chat_users').find('.online_user').hide();

					/*$.get(
						posturl.replace(/postMessage/, 'privateRoom-listUsers') + '&sub=' + id,
						function(response)
						{
							$('#chat_users').css('visibility', 'hidden');
							response = typeof response == 'object' ? response : $.getAsObject(response);

							$.each(response, function() {
								var element = $('#chat_users').find('.user_' + this);
								if (!element.hasClass('hidden_entry')) {
									element.show();
								}
								else {
									element.hide();
								}
							});
							$('.hidden_entry').hide();
							if ($('.online_user:visible').length == 0) {
								$('.no_users').show();
							}
							else {
								$('.no_users').hide();
							}
							$('#chat_users').css('visibility', 'visible');
						},
						'json'
					);*/
				}

				if ($('.online_user:visible').length == 0) {
					$('.no_users').show();
				}
				else {
					$('.no_users').hide();
				}

				subRoomId = id;

				return $(this);
			}
		}
	
		if ( methods[method] ) {
			return methods[method].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
			return methods.init.apply( this, arguments );
		} else {
			$.error( 'Method ' +  method + ' does not exist on jQuery.ilChatMessageArea' );
		}
  
	};


	function scrollChatArea(container) {
		if($('#chat_auto_scroll').attr('checked')) {
			$(container).parent().animate({
				scrollTop: $(container).height()
			}, 5);
		}
	}


})(jQuery)
