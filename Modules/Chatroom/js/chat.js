(function($) {

	$(document).click(function() {
	    $('.dropdown-menu.menu').hide();
	    $('.menu_attached').removeClass('menu_attached');
	});
	
	var iconsByType = {
		user: 'templates/default/images/icon_usr.svg',
		room: 'templates/default/images/icon_chtr.svg'
	}


	var inArray = function(ar, value) {
		if (ar && (typeof ar == 'object' || typeof ar == 'array')) {
			for(var i in ar) {
				if (ar[i] == value) {
					return true;
				}
			}
		}
		return false;
	}

	$.fn.ilChatDialog = function (method) {
		var applyStyle = function (dialog) {
			dialog.css('position', 'absolute');
		}

		var methods = {
			init: function (params) {
				var $content = $(this);

				var defaultButtons = (params.defaultButtons === false) ? [] : [
					{
						id:      'ok',
						label:   translate('ok'),
						callback:function (e) {
							var close = true;
							if (typeof params.positiveAction == 'function') {
								close = params.positiveAction.call($content, e);
							}
							if (typeof close == 'undefined' || close) {
								$content.ilChatDialog('close');
							}
						}
					},
					{
						id:      'cancel',
						label:   translate('cancel'),
						callback:function (e) {
							var close = true;
							if (typeof params.negativeAction == 'function') {
								close = params.negativeAction.call($content, e);
							}
							if (typeof close == 'undefined' || close) {
								$content.ilChatDialog('close');
							}
						}
					}
				];

				var properties = $.extend(true, {}, {
					title:           '',
					parent:          $('body'),
					position:        null,
					buttons:         defaultButtons,
					disabled_buttons:[]
				}, params);

				var dialog = $('<div class="ilChatDialog">');

				applyStyle(dialog);

				$(this).data('ilChatDialog', $.extend(properties, {
					_dialog:dialog,
					_parent:$(this).parent()
				}));

				if (properties.title) {
					var title = $('<h3>').text(properties.title).addClass('chat_block_title');
					dialog.append(title);
				}

				$('#modal_alpha').remove();

				$('<div id="modal_alpha" class="chat_modal_overlay">').appendTo($('body'));

				var dialogBody = $('<div class="ilChatDialogBody">').appendTo(dialog);
				$(this).appendTo(dialogBody).show();

				dialog.appendTo(properties.parent);

				if (properties.buttons) {
					var dialogButtons = $('<div class="ilChatDialogButtons">').appendTo(dialog);
					$.each(properties.buttons, function () {
						// IE: properties.disabled_buttons is of type object instead of array
						//if (this.id && properties.disabled_buttons.indexOf(this.id) >= 0) {
						if (this.id && inArray(properties.disabled_buttons, this.id)) {
							return;
						}
						$('<input type="button" class="btn btn-default btn-sm">')
							.click(this.callback)
							.val(this.label)
							.appendTo(dialogButtons);
					});
				}

				if (!properties.position) {
					properties.position = {
						x:($(window).width() - dialog.width()) / 2,
						y:($(window).height() - dialog.height()) / 2
					}
				}

				dialog.css('left', properties.position.x)
					.css('top', properties.position.y);

				return $(this);
			},
			close:function () {
				var data = $(this).data('ilChatDialog');

				if (typeof data.close == 'function') {
					data.close();
				}

				if (data._parent) {
					$(this).appendTo($(data._parent)).hide();
				}
				$('#modal_alpha').remove();
				$(data._dialog).remove();
			}
		}

		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('Method ' + method + ' does not exist on jQuery.ilChatDialog');
		}
	}

	function getMenuLine(label, callback, icon) {
		var line = $('<li></li>');
		var content = $('<a class="" href="#">'+(icon ? ('<img style="margin-right: 8px" src="'+icon+'"/>'): '')+'<span class="xsmall">'+label+'</span></a>');
		line.append(content);
		if (callback) {
			line.bind('click', function(ev) {
				$(this).parents('.menu').hide();
				menuContainer.data('ilChatMenu')._attatched.removeClass('menu_attached');
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
					menuContainer = $('<ul class="dropdown-menu menu pull-right" role="menu"></ul>')
						.appendTo($(this));
				}

				if ($(this).hasClass('menu_attached')) {
					$(this).removeClass('menu_attached');
					menuContainer.hide();
					return;
				}
				else if (menuContainer.is(':visible')) {
					menuContainer.hide();
				}

				menuContainer.find('li').remove();

				$(this).addClass('menu_attached');

				var table = menuContainer;

				$.each(menuitems, function() {
					var line = getMenuLine(this.separator ? '<hr/>' : this.label, this.callback, this.icon).appendTo(table);
					if (this.addClass) {
						line.find('span').addClass(this.addClass);
					}
				});

				menuContainer.data('ilChatMenu', {
					_attatched: this
				})

				menuContainer.show();
			}
		}

		if ( methods[method] ) {
			return methods[method].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
			return methods.init.apply( this, arguments );
		} else {
			$.error( 'Method ' +  method + ' does not exist on jQuery.ilChatList' );
		}
	}

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

		var menuContainer = $('<ul class="dropdown-menu menu pull-right" role="menu"></ul>')
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
					return $(this);
				}
                    
				var line = $(
					'<p class="listentry '+options.type+'_'+options.id+' online_user"><img src="'+iconsByType[options.type]+'" />&nbsp;' +
					'<button onclick="this.blur(); return false;" class="btn btn-default dropdown-toggle btn-sm" data-toggle="dropdown" data-container="body">' +
					'<span class="label">' + options.label + '</span> ' +
					'<span class="caret" alt=""></span>' +
					'</button>' +
					'</p>');

				if (typeof options.hide != 'undefined' && options.hide == true) {
					line.addClass('hidden_entry');
				} 
		    
				line.data('ilChatList', options);
				
				var $this = $(this);
				$this.data('ilChatList')._index['id_' + options.id] = line;

				line.bind('click', function(e) {
				    
					e.preventDefault();
					e.stopPropagation();
				    
					if ($(this).hasClass('menu_attached')) {
						$(this).removeClass('menu_attached');
						menuContainer.hide();
						return;
					}
					else if (menuContainer.is(':visible')) {
						menuContainer.hide();
					}

					menuContainer.find('li').remove();
					var data = $(this).data('ilChatList');
					if (data.type == 'user' && data.id == personalUserInfo.userid) {
						return;
					}

					$(this).addClass('menu_attached');

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
					
					menuContainer.appendTo(line);

					menuContainer.data('ilChat', {
						context: $(this).data('ilChatList')
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
					line.remove();
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
			getAll: function() {
				var result = [];
				$.each($(this).data('ilChatList')._index, function() {
					result.push(this.data('ilChatList'));
				});

				result.sort(function(a, b) {
					return (a.label < b.label) ? -1 : 1;
				});

				return result;
			}
		}
	
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

					var data = container.data('ilChatMessageArea');
                        
					var line = $('<div class="messageLine chat"></div>')
					.addClass((message['public'] || message['public'] == undefined) ? 'public' : 'private');
					if (message.message && message.message.message) {
					    message = message.message;
					}
					switch(message.type) {
						case 'message':
							var content;
							try {
							    content = JSON.parse(message.message);
							}
							catch (ex) {
							    //console.log(ex);
							    return true;
							}
							
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
								.append($('<span class="chat content username"></span>').append(message.user.username));

							if (message.recipients) {
								var parts = message.recipients.split(',');
								for (var i in parts) {
									if (parts[i] != message.user.id) {
										var data = $('#chat_users').ilChatList('getDataById', parts[i]);
										if (data) {
											line.append($('<span class="chat recipient">@</span>').append(data.label))
										}
										else {
											line.append($('<span class="chat recipient">@</span>').append('unkown'))
										}
									}
								}
							}

							var messageSpan = $('<span class="chat content message"></span>');
								messageSpan.text(messageSpan.text(content.content).text())
									.html(replaceSmileys(messageSpan.text()));
							line.append($('<span class="chat content messageseparator">:</span>'))
								.append(messageSpan);
								

							for(var i in content.format) {
								if (i != 'color')
									messageSpan.addClass( i + '_' + content.format[i]);
							}

							messageSpan.css('color', content.format.color);

							if (data && data.id != subRoomId) {
								$('#room_' + data.id).addClass('new_events');
							}

							break;
						case 'connected':
							if (message.login || (message.message.users[0] && message.message.users[0].login)) {
							    /*line
							    .append($('<span class="chat content date"></span>').append('(' + formatISOTime(message.timestamp || message.message.timestamp) + ') '))
							    .append($('<span class="chat content username"></span>').append(message.login || message.message.users[0].login))
							    .append($('<span class="chat content messageseparator">:</span>'))
							    .append($('<span class="chat content message"></span>').append(translate('connect')));*/
								line
								    .append($('<span class="chat"></span>').append(translate('connect', {username: message.login})));
								line.addClass('notice');
							}
							break;
						case 'disconnected':
							if (message.login || (message.message.users[0] && message.message.users[0].login)) {
							    /*line
							    .append($('<span class="chat content date"></span>').append('(' + formatISOTime(message.timestamp || message.message.timestamp) + ') '))
							    .append($('<span class="chat content username"></span>').append(message.login || message.message.users[0].login))
							    .append($('<span class="chat content messageseparator">:</span>'))
							    .append($('<span class="chat content message"></span>').append(translate('disconnected')));*/
								line
								    .append($('<span class="chat"></span>').append(translate('disconnected', {username: message.login})));
								line.addClass('notice');
							}
							break;
						case 'private_room_entered':
							if (message.login || (message.message.users[0] && message.message.users[0].login)) {
							    line
							    .append($('<span class="chat content date"></span>').append('' + formatISOTime(message.timestamp || message.message.timestamp) + ', '))
							    .append($('<span class="chat content username"></span>').append(message.login || message.message.users[0].login))
							    .append($('<span class="chat content messageseparator">:</span>'))
							    .append($('<span class="chat content message"></span>').append(translate('connect')));
							}
							break;
						case 'private_room_left':
						case 'notice':
							line
							    .append($('<span class="chat"></span>').append(message.message));
							line.addClass('notice');
							break;
						case 'error':
							line
							.append($('<span class="chat"></span>').append(message.message));
							line.addClass('error');
							break;
						case 'userjustkicked':
							break;
					}

					container.append(line);
				})

                    
				return $(this);
			},
			hasContent: function(id) {
				return $(this).data('ilChatMessageArea')._scopes['id_' + id].find('div').length > 0;
			},
			clearMessages: function(id) {
				$(this).data('ilChatMessageArea')._scopes['id_' + id].find('div').html('');
			},
			show: function(id, posturl) {
				var scopes = $(this).data('ilChatMessageArea')._scopes;
                    
				$.each(scopes, function() {
					$(this).hide();
				});
                    
				scopes['id_' + id].show();
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
						$('#chat_messages').ilChatMessageArea('show', 0);
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

					$.get(
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
					);
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
})(jQuery)
