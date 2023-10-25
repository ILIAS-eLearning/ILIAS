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
 *
 ******************************************************************** */

(function ($) {
  const iconsByType = {
    user: 'templates/default/images/standard/icon_usr.svg',
    room: 'templates/default/images/standard/icon_chtr.svg',
  };

  const inArray = function (ar, value) {
    if (ar && (typeof ar === 'object' || typeof ar === 'array')) {
      for (const i in ar) {
        if (ar[i] == value) {
          return true;
        }
      }
    }
    return false;
  };

  $.fn.ilChatDialog = function (method) {
    const methods = {
      init(params) {
        const $content = $(this);

        const defaultButtons = (params.defaultButtons === false) ? [] : [
          {
            id: 'ok',
            label: il.Chatroom.translate('ok'),
            callback(e) {
              let close = true;
              if (typeof params.positiveAction === 'function') {
                close = params.positiveAction.call($content, e);
              }
              if (typeof close === 'undefined' || close) {
                $content.ilChatDialog('close');
              }
            },
          },
          {
            id: 'cancel',
            label: il.Chatroom.translate('cancel'),
            callback(e) {
              let close = true;
              if (typeof params.negativeAction === 'function') {
                close = params.negativeAction.call($content, e);
              }
              if (typeof close === 'undefined' || close) {
                $content.ilChatDialog('close');
              }
            },
          },
        ];

        const properties = $.extend(true, {}, {
          title: '',
          buttons: defaultButtons,
          disabled_buttons: [],
        }, params);

        const dialogBody = $('<div class="ilChatDialogBody">');
        const buttons = {};

        if (properties.buttons) {
          $.each(properties.buttons, function () {
            const btn = this;

            // IE: properties.disabled_buttons is of type object instead of array

            if (btn.id && inArray(properties.disabled_buttons, btn.id)) {
              return;
            }

            buttons[btn.id] = {
              type: 'button',
              label: this.label,
              className: 'btn btn-default',
              callback(e, $modal) {
                if (typeof btn.callback === 'function') {
                  btn.callback();
                }
              },
            };
          });
        }

        $(this).appendTo(dialogBody).show();

        const $modal = il.Modal.dialogue({
          show: true,
          header: properties.title || null,
          body: $(this),
          buttons: buttons || [],
        });

        $(this).show();

        $(this).data('ilChatDialog', $.extend(properties, {
          _modal: $modal,
          _parent: $(this).parent(),
        }));

        return $(this);
      },
      close() {
        const data = $(this).data('ilChatDialog');

        if (typeof data.close === 'function') {
          data.close();
        }

        data._modal.hide();
      },
    };

    if (methods[method]) {
      return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
    } if (typeof method === 'object' || !method) {
      return methods.init.apply(this, arguments);
    }
    $.error(`Method ${method} does not exist on jQuery.ilChatDialog`);
  };

  function getMenuLine(label, callback, icon) {
    const line = $('<li></li>');
    const content = $(`<a class="" href="#">${icon ? (`<img style="margin-right: 8px" src="${icon}"/>`) : ''}<span class="xsmall">${label}</span></a>`);
    line.append(content);
    if (callback) {
      line.on('click', function (ev) {
        $(this).parents('.menu').hide();
        callback.call($(this).parents('.menu'));
        ev.preventDefault();
        ev.stopPropagation();
        return false;
      });
    }
    return line;
  }

  let menuContainer;

  $.fn.ilChatMenu = function (method) {
    const methods = {
      init(menuitems) {

      },
      show(menuitems, alignToRight) {
        if (!menuContainer) {
          menuContainer = $('<ul class="dropdown-menu menu" role="menu"></ul>')
            .appendTo($(this));
        }

        if (alignToRight != undefined && alignToRight) {
          menuContainer.addClass('pull-right');
        } else {
          menuContainer.removeClass('pull-right');
        }

        menuContainer.find('li').remove();

        const table = menuContainer;

        $.each(menuitems, function () {
          const line = getMenuLine(this.separator ? '<hr/>' : this.label, this.callback, this.icon).appendTo(table);
          if (this.addClass) {
            line.find('span').addClass(this.addClass);
          }
        });

        menuContainer.data('ilChatMenu', {
          _attatched: this,
        });

        menuContainer.show();
      },
    };

    if (methods[method]) {
      return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
    } if (typeof method === 'object' || !method) {
      return methods.init.apply(this, arguments);
    }
    $.error(`Method ${method} does not exist on jQuery.ilChatList`);
  };

  $.fn.ilChatUserList = function (method) {
    function getUserRow(user) {
      let tpl = $('#ilChatUserRowTemplate').html();
      tpl = tpl.replace(/\[\[USERNAME\]\]/g, user.label);

      tpl = $(tpl.replace(/\[\[INDEX\]\]/g, `${user.type}_${user.id}`));
      tpl.find('.media-object').attr('src', user.image.src);

      return $(tpl)
        .addClass(`${user.type}_${user.id}`)
        .addClass('online_user');
    }

    function getUserActionRow(label, callback) {
      let tpl = $('#ilChatUserRowAction').html();

      tpl = $(tpl.replace(/\[\[LABEL\]\]/g, label));

      if ($.isFunction(callback)) {
        tpl.find('a').on('click', function (e) {
          e.stopPropagation();
          e.preventDefault();
          callback.call($(this).closest('.dropdown-menu').data('ilChat').context);
        });
      }

      return tpl;
    }

    const methods = {
      init(menuitems) {
        $(this).data('ilChatUserList', {
          _index: {},
          _menuitems: menuitems,
        });
      },
      add(options) {
        if ($(this).data('ilChatUserList')._index[`id_${options.id}`]) {
          if (options.label != undefined) {
            $(this).data('ilChatUserList')._index[`id_${options.id}`].find('.media-heading').html(options.label);
          }
          return $(this);
        }

        const line = $(getUserRow(options));

        if (typeof options.hide !== 'undefined' && options.hide == true) {
          line.addClass('hidden_entry');
        }

        line.data('ilChatUserList', options);

        const $this = $(this);

        $this.data('ilChatUserList')._index[`id_${options.id}`] = line;

        if (il.Chatroom.getUserInfo().id == options.id) {
          line.addClass('self');
        }

        const menu = line.find('.dropdown-menu');

        menu.find('li').remove();
        $.each($this.data('ilChatUserList')._menuitems, function (i) {
          if (this.permission == undefined) {
            menu.append(function (row) {
              if (i === 0) {
                row.find('.arrow-down').removeClass('ilNoDisplay');
              }
              return row;
            }(getUserActionRow(this.label, this.callback)));
          } else if (
            (il.Chatroom.getUserInfo().moderator && inArray(this.permission, 'moderator') >= 0)
						|| (il.Chatroom.getUserInfo().id == data.owner && inArray(this.permission, 'owner') >= 0)
          ) {
            menu.append(function (row) {
              if (i === 0) {
                row.find('.arrow-down').removeClass('ilNoDisplay');
              }
              return row;
            }(getUserActionRow(this.label, this.callback)));
          }
        });
        menu.data('ilChat', {
          context: line.data('ilChatUserList'),
        });

        $(this).append(line);

        if (line.hasClass('hidden_entry')) {
          line.hide();
        }

        if ($('.online_user:visible').length == 0) {
          $('.no_users').show();
        } else {
          $('.no_users').hide();
        }

        return $(this).ilChatUserList('sort');
      },
      sort() {
        const tmp = [];

        $.each($(this).data('ilChatUserList')._index, function (i) {
          tmp.push({ id: i, data: this });
        });

        tmp.sort((a, b) => ((a.data.data('ilChatUserList').label < b.data.data('ilChatUserList').label) ? -1 : 1));

        for (let i = 0; i < tmp.length; ++i) {
          $(this).append(tmp[i].data);
        }

        return $(this);
      },
      removeById(id) {
        const line = $(this).data('ilChatUserList')._index[`id_${id}`];
        if (line) {
          const data = line.data('ilChatUserList');
          $(`.${data.type}_${id}`).remove();
          if ($('.online_user:visible').length == 0) {
            $('.no_users').show();
          } else {
            $('.no_users').hide();
          }
          delete $(this).data('ilChatUserList')._index[`id_${id}`];
        }
        return $(this);
      },
      getDataById(id) {
        return $(this).data('ilChatUserList')._index[`id_${id}`] ? $(this).data('ilChatUserList')._index[`id_${id}`].data('ilChatUserList') : undefined;
      },
      setNewEvents(newEvents) {
        const data = $(this).data('ilChatUserList')._index.id_0.data('ilChatUserList');
        if (data) {
          data.new_events = newEvents;
        }
      },
      getAll() {
        const result = [];

        $.each($(this).data('ilChatUserList')._index, function () {
          result.push(this.data('ilChatUserList'));
        });

        result.sort((a, b) => ((a.label < b.label) ? -1 : 1));

        return result;
      },
      clear() {
        $('#chat_users').find('div').remove();

        $(this).data('ilChatUserList', {
          _index: {},
          _menuitems: $(this).data('ilChatUserList')._menuitems,
        });
      },
    };

    if (methods[method]) {
      return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
    } if (typeof method === 'object' || !method) {
      return methods.init.apply(this, arguments);
    }
    $.error(`Method ${method} does not exist on jQuery.ilChatUserList`);
  };

  $.fn.ilChatList = function (method) {
    function getMenuLine(label, callback) {
      const line = $('<li></li>')
        .append(
          $(`<a href="#"><span class="small">${label}</span></a>`)
            .click(function (e) {
              $(this).parents('.menu').hide();
              e.stopPropagation();
              e.preventDefault();
              callback.call($(this).parents('.menu').data('ilChat').context);
            }),
        );

      return line;
    }

    const menuContainer = $('<ul class="dropdown-menu menu" role="menu"></ul>')
      .appendTo($('body'));

    const methods = {
      init(menuitems) {
        $(this).data('ilChatList', {
          _index: {},
          _menuitems: menuitems,
        });
      },
      add(options) {
        if ($(this).data('ilChatList')._index.id_0) {
          if (options.label != undefined) {
            $(this).data('ilChatList')._index.id_0.find('.label').html(options.label);
          }
          return $(this);
        }

        const line = $(
          `<div class="listentry ${options.type}_0` + ` online_user"><img src="${iconsByType[options.type]}" />&nbsp;`
						+ '<div class="btn-group">'
							+ '<button class="btn btn-default dropdown-toggle" data-toggle="dropdown" data-container="body">'
							+ `<span class="label">${options.label}</span> `
							+ '<span class="caret"></span>'
							+ '</button>'
						+ '</div>'
					+ '</div>',
        );

        if (typeof options.hide !== 'undefined' && options.hide == true) {
          line.addClass('hidden_entry');
        }

        line.data('ilChatList', options);

        const $this = $(this);
        $this.data('ilChatList')._index.id_0 = line;

        line.find('button').on('click', (e) => {
          e.preventDefault();
          e.stopPropagation();

          menuContainer.find('li').remove();

          const data = line.data('ilChatList');

          $.each($this.data('ilChatList')._menuitems, function () {
            if (this.permission == undefined) {
              menuContainer.append(getMenuLine(this.label, this.callback));
            } else if (
              il.Chatroom.getUserInfo().moderator && inArray(this.permission, 'moderator') >= 0
							|| il.Chatroom.getUserInfo().id == data.owner && inArray(this.permission, 'owner') >= 0
            ) {
              menuContainer.append(getMenuLine(this.label, this.callback));
            }
          });

          menuContainer.appendTo(line.find('.btn-group'));

          menuContainer.data('ilChat', {
            context: line.data('ilChatList'),
          });

          menuContainer.show();
        });

        if (options.type == 'room' && options.owner == il.Chatroom.getUserInfo().id) {
          line.addClass('self');
        }

        $(this).append(line);
        if (line.hasClass('hidden_entry')) {
          line.hide();
        }

        return $(this).ilChatList('sort');
      },
      sort() {
        const tmp = [];
        $.each($(this).data('ilChatList')._index, function (i) {
          tmp.push({ id: i, data: this });
        });

        tmp.sort((a, b) => ((a.data.data('ilChatList').label < b.data.data('ilChatList').label) ? -1 : 1));
        for (let i = 0; i < tmp.length; ++i) {
          $(this).append(tmp[i].data);
        }

        return $(this);
      },
      removeById(id) {
        const line = $(this).data('ilChatList')._index[`id_${id}`];
        if (line) {
          const data = line.data('ilChatList');
          // line.remove();
          if (data.type == '') {
            $(`${data.type}_${id}`).remove();
          }
          delete $(this).data('ilChatList')._index[`id_${id}`];
        }
        return $(this);
      },
      getDataById(id) {
        return $(this).data('ilChatList')._index[`id_${id}`] ? $(this).data('ilChatList')._index[`id_${id}`].data('ilChatList') : undefined;
      },
      setNewEvents(newEvents) {
        const data = $(this).data('ilChatList')._index.id_0.data('ilChatList');
        if (data) {
          data.new_events = newEvents;
        }
      },
      getAll() {
        const result = [];
        $.each($(this).data('ilChatList')._index, function () {
          result.push(this.data('ilChatList'));
        });

        result.sort((a, b) => ((a.label < b.label) ? -1 : 1));

        return result;
      },
      clear() {
        menuContainer.html('');
        $(this).data('ilChatList', {
          _index: {},
          _menuitems: $(this).data('ilChatList')._menuitems,
        });
      },
    };

    if (methods[method]) {
      return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
    } if (typeof method === 'object' || !method) {
      return methods.init.apply(this, arguments);
    }
    $.error(`Method ${method} does not exist on jQuery.ilChatList`);
  };

  const lastHandledDate = {};
  $.fn.ilChatMessageArea = function (method) {
    const scrollChatArea = function (container, state) {
      if (state.scrolling) {
        $(container).parent().animate({
          scrollTop: $(container).height(),
        }, 5);
      }
    };

    const methods = {
      init(s) {
        $(this).data('ilChatMessageArea', {
          _scopes: {},
          _typeInfos: {},
          _state: s,
        });

        $(this).data('state', s);
      },
      addScope(scope) {
        const tmp = $('<div class="messageContainer" aria-live="off">');
        $(this).data('ilChatMessageArea')._scopes.id_0 = tmp;
        $(this).append(tmp);
        tmp.data('ilChatMessageArea', scope);
        tmp.hide();

        const fader = $('<div class="fader">');
        $(this).data('ilChatMessageArea')._typeInfos.id_0 = fader;
        $(this).append(fader);
        fader.data('ilChatMessageArea', scope);
        fader.append($('<div class="typing-info" aria-live="off">'));
        fader.hide();
      },
      addTypingInfo(messageObject, text) {
        let containers;

        containers = [$(this).data('ilChatMessageArea')._typeInfos.id_0];

        $.each(containers, function () {
          const container = this;

          if (!container || container == window) {
            return;
          }

          container.find('.typing-info').text(text);
        });
      },
      addMessage(message) {
        let containers; const
          msgArea = $(this);
        containers = [$(this).data('ilChatMessageArea')._scopes.id_0];

        $.each(containers, function () {
          const container = this;

          if (!container || container == window) {
            return;
          }

          const line = $('<div class="messageLine chat"></div>')
            .addClass((message.target != undefined && !message.target.public) ? 'private' : 'public');

          switch (message.type) {
            case 'message':
              var { content } = message;

              if (message.from == undefined) {
                const legacyMessage = JSON.parse(message.message);
                content = legacyMessage.content;
                message.format = legacyMessage.format;
                message.from = message.user;

                if (message.timestamp.toString().length > 13) { // Max 32-Bit Integer.
                  message.timestamp = parseInt(message.timestamp.toString().substring(0, 13));
                }
              }

              var messageDate = new Date(message.timestamp);

              if (typeof lastHandledDate.scope === 'undefined'
								|| lastHandledDate.scope == null
								|| lastHandledDate.scope.getDate() != messageDate.getDate()
								|| lastHandledDate.scope.getMonth() != messageDate.getMonth()
								|| lastHandledDate.scope.getFullYear() != messageDate.getFullYear()) {
                container.append($(`<div class="messageLine chat dateline"><span class="chat content date">${il.Chatroom.formatISODate(message.timestamp)}</span><span class="chat content username"></span><span class="chat content message"></span></div>`));
              }
              lastHandledDate.scope = messageDate;

              line.append($('<span class="chat content date"></span>').append(`${il.Chatroom.formatISOTime(message.timestamp)}, `))
                .append($('<span class="chat content username"></span>').append(message.from.username));

              if (message.target) {
                if (message.target.username != '') {
                  line.append($('<span class="chat recipient">@</span>').append(message.target.username));
                } else {
                  line.append($('<span class="chat recipient">@</span>').append('unknown'));
                }
              }

              var messageSpan = $('<span class="chat content message"></span>');
              messageSpan.text(messageSpan.text(content).text());
              line.append($('<span class="chat content messageseparator">:</span>'))
                .append(messageSpan);

              $('.room_0').addClass('new_events');

              break;
            case 'connected':
              if (message.login || (message.users[0] && message.users[0].login)) {
                line.append($('<span class="chat"></span>').append(il.Chatroom.translate('connect', { username: message.users[0].login })));
                line.addClass('notice');
                if (!msgArea.data('state').show_auto_msg) {
                  line.addClass('ilNoDisplay');
                }
              }
              break;
            case 'disconnected':
              if (message.login || (message.users[0] && message.users[0].login)) {
                line.append($('<span class="chat"></span>').append(il.Chatroom.translate('disconnected', {	username: message.users[0].login })));
                line.addClass('notice');
                if (!msgArea.data('state').show_auto_msg) {
                  line.addClass('ilNoDisplay');
                }
              }
              break;
            case 'private_room_entered':
              if (message.login || (message.users[0] && message.users[0].login)) {
                line
                  .append($('<span class="chat content date"></span>').append(`${il.Chatroom.formatISOTime(message.timestamp)}, `))
                  .append($('<span class="chat content username"></span>').append(message.login || message.users[0].login))
                  .append($('<span class="chat content messageseparator">:</span>'))
                  .append($('<span class="chat content message"></span>').append(il.Chatroom.translate('connect', { username: message.users[0].login })));
              }
              break;
            case 'private_room_left':
            case 'notice':
              line.append($('<span class="chat"></span>').append(message.content));
              line.addClass('notice');
              if (!msgArea.data('state').show_auto_msg) {
                line.addClass('ilNoDisplay');
              }
              break;
            case 'error':
              line.append($('<span class="chat"></span>').append(message.content));
              line.addClass('error');
              break;
            case 'userjustkicked':
              break;
          }

          container.append(line);
          scrollChatArea(container, msgArea.data('state'));
        });

        return $(this);
      },
      hasContent(id) {
        return $(this).data('ilChatMessageArea')._scopes[`id_${id}`].find('div').length > 0;
      },
      clearMessages() {
        $(this).data('ilChatMessageArea')._scopes.id_0.find('div').html('');
      },
      show(posturl, leaveCallback) {
        const scopes = $(this).data('ilChatMessageArea')._scopes;
        const typeInfos = $(this).data('ilChatMessageArea')._typeInfos;
        const msgArea = $(this);

        $.each(scopes, function () {
          $(this).attr('aria-live', 'off').hide();
        });

        $.each(typeInfos, function () {
          $(this).hide().find('[aria-live]').attr('aria-live', 'off');
        });

        scopes.id_0.attr('aria-live', 'polite').show();
        typeInfos.id_0.show().find('[aria-live]').attr('aria-live', 'polite');

        scrollChatArea(scopes.id_0, msgArea.data('state'));
			    $('.current_room_title').text(scopes.id_0.data('ilChatMessageArea').title);

        $('.in_room').removeClass('in_room');

        $('.room_0').addClass('in_room');

			    $('#chat_users').find('.online_user').not('.hidden_entry').show();

        if ($('.online_user:visible').length == 0) {
          $('.no_users').show();
        } else {
          $('.no_users').hide();
        }

        msgArea
          .off('auto-message:toggle')
          .off('msg-scrolling:toggle')
          .on('auto-message:toggle', (e, isActive, url) => {
            const state = msgArea.data('state');

            let msgState = 1;
            if (isActive) {
              state.show_auto_msg = true;
              $('#chat_messages .messageLine.notice').removeClass('ilNoDisplay');
            } else {
              msgState = 0;
              state.show_auto_msg = false;
              $('#chat_messages .messageLine.notice').addClass('ilNoDisplay');
            }

            msgArea.data('state', state);

            $.ajax({
              type: 'POST',
              url,
              data: { state: msgState },
            });
          })
          .on('msg-scrolling:toggle', (e, isActive) => {
            const state = msgArea.data('state');

            if (isActive) {
              state.scrolling = true;
            } else {
              state.scrolling = false;
            }
          });

        return $(this);
      },
    };

    if (methods[method]) {
      return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
    } if (typeof method === 'object' || !method) {
      return methods.init.apply(this, arguments);
    }
    $.error(`Method ${method} does not exist on jQuery.ilChatMessageArea`);
  };
}(jQuery));
