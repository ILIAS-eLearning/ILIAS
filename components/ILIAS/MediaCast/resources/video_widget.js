il = il || {};
il.VideoWidget = il.VideoWidget || {};
(function ($, il) {
  il.VideoWidget = (function ($) {
    const t = il.VideoWidget;

    t.widget = [];
    t.progress_running = false;
    t.wrapper_ids = [];

    const _boot = () => {
      $(() => {
        /* This fixes e.g. chrome on safari. The player
           reacts to resize events. If the orientation is changed on a
           tablet, the resize event is fired before rendering is updated - too early.
           The player will keep its old size.
           However chrome fires an "orientationchange" after the rendering has been updated.
           So we fire "resize" again, when the "orientationchange" event occurs.
         */
        window.addEventListener('orientationchange', () => {
          window.setTimeout(() => {
            window.dispatchEvent(new Event('resize'));
          }, 1);
        }, false);
      });
    };
    _boot();

    let iframeOrigin = '*';

    function postMessageToIframe(iframe, command, value) {
      iframe.contentWindow.postMessage(JSON.stringify({
        event: 'command',
        func: command,
        args: [value],
      }), iframeOrigin);
    }

    const initExternalProgress = (iframe, wrapper_id) => {
      iframe.contentWindow.postMessage({ event: 'listening' }, "*");
      iframe.contentWindow.postMessage('{"event": "listening"}', "*");
      iframe.contentWindow.postMessage(JSON.stringify({ method: "addEventListener", value: "playProgress" }), "*");

      let videoDuration;

      window.addEventListener("message", (event) => {
        if (event.origin.includes("youtube.com")) {

          const data = JSON.parse(event.data);

          // Get video duration once
          if (data.event === "infoDelivery" && data.info && data.info.duration) {
            videoDuration = data.info.duration;
          }

          // Track playback progress
          if (data.event === "infoDelivery" && data.info && data.info.currentTime && videoDuration > 0) {
            const currentTime = data.info.currentTime;
            const cb = t.widget[wrapper_id].progress_cb;
            if (cb) {
              cb(wrapper_id, currentTime, videoDuration, currentTime === videoDuration);
            }
          }
        }
        if (event.origin.includes("vimeo.com")) {
          const data = JSON.parse(event.data);
          // Check if the message is a playProgress event
          if (data && data.event === "playProgress") {
            const currentTime = data.data.seconds; // Current playback time in seconds
            const duration = data.data.duration;   // Total duration of the video
            const cb = t.widget[wrapper_id].progress_cb;
            if (cb) {
              cb(wrapper_id, currentTime, duration, currentTime === duration);
            }
          }
        }
      });
    };

    const progress = () => {

      // console.log("monitoring progress");
      // for all wrappers
      t.wrapper_ids.forEach((wrapper_id, i, a) => {

        const cb = t.widget[wrapper_id].progress_cb;
        if (!cb) {
          return;
        }
        // get video element
        const vid = document.getElementById(wrapper_id).querySelector('video');
        if (vid) {
          cb(wrapper_id, vid.currentTime, vid.duration, vid.currentTime === vid.duration);
        }

        /*
        // get player
        const p = t.widget[wrapper_id].player;
        // if the wrapper defines a progress callback, call it
        if (t.widget[wrapper_id].progress_cb) {
          // console.log(p);
          // get current time, duration and ended information to callback
          t.widget[wrapper_id].progress_cb(wrapper_id,
          p.getCurrentTime(), p.node.duration, p.node.ended);
        } */
      });
      setTimeout(progress, 1000);
    };

    // init player
    const init = (wrapper_id, tpl) => {
      t.widget[wrapper_id] = {
        tpl,
      };
      if (!t.progress_running) {
        progress();
        t.progress_running = true;
      }
      t.wrapper_ids.push(wrapper_id);
    };

    const setMeta = (wrapper_id, title, description, downloadUrl) => {
      const $wrap = $(`#${wrapper_id}`);
      $wrap.parent().find("[data-elementtype='title']").html(title);
      if (description !== '') {
        $wrap.parent().find("[data-elementtype='description']").html(description);
        $wrap.parent().find("[data-elementtype='description-wrapper']").removeClass('ilNoDisplay');
        // $wrap.parent().find("[data-elementtype='description']").addClass("ilNoDisplay");
        $wrap.parent().find("[data-elementtype='description-trigger']").removeClass('ilNoDisplay');
      } else {
        $wrap.parent().find("[data-elementtype='description']").html('');
        // $wrap.parent().find("[data-elementtype='description-wrapper']").addClass("ilNoDisplay");
      }
      const downEl = $wrap.parent().find("[data-elementtype='download']");
      if (downloadUrl !== '') {
        downEl.closest('a').removeClass('ilNoDisplay');
        downEl.closest('a').attr('href', downloadUrl);
      } else {
        downEl.closest('a').addClass('ilNoDisplay');
        downEl.closest('a').attr('href', '#');
      }
    };

    // load file into player and show it
    const loadFile = (wrapper_id, video_data, play, progress_cb) => {
      if (!video_data.renderUrl) {
        //console.trace();
        return;
      }

      //const content = t.widget[wrapper_id].tpl;
      const wrapperEl = document.getElementById(wrapper_id);

      il.repository.core.fetchReplaceInner(
        wrapperEl,
        video_data.renderUrl,
        {},
        () => {
          const iframe = wrapperEl.querySelector('iframe');
          const vid = wrapperEl.querySelector('video');
          if (vid) {
            if (play) {
              vid.play();
            }
          } else {
            iframe.addEventListener("load", () => {
              setTimeout(() => {
                initExternalProgress(iframe, wrapper_id);
                // currently does not work
                if (play) {
                  /*iframe.contentWindow.postMessage({ event: 'play' }, "*");
                  iframe.contentWindow.postMessage({ event: 'playVideo' }, "*");
                  iframe.contentWindow.postMessage('{"event": "play"}', "*");
                  iframe.contentWindow.postMessage('{"event": "playVideo"}', "*");*/
                  iframe.contentWindow.postMessage(JSON.stringify({ method: "play"}), "*");
                }
              }, 500);    /* this seems to be needed for vimeo */
            });
          }
        },
      );

      setMeta(
        wrapper_id,
        video_data.title,
        video_data.description,
        video_data.download_url
      );

      t.widget[wrapper_id].progress_cb = progress_cb;
    };

    const setPreviousCallback = (wrapper_id, pcb) => {
      t.widget[wrapper_id].previous = pcb;
    };

    const setNextCallback = (wrapper_id, ncb) => {
      t.widget[wrapper_id].next = ncb;
    };

    const previous = (wrapper_id) => {
      if (t.widget[wrapper_id].previous) {
        t.widget[wrapper_id].previous();
      }
    };

    const next = (wrapper_id) => {
      if (t.widget[wrapper_id].next) {
        t.widget[wrapper_id].next();
      }
    };

    return {
      init,
      loadFile,
      setMeta,
      setPreviousCallback,
      setNextCallback,
      previous,
      next,
    };
  }($));
}($, il));

il.VideoPlaylist = il.VideoPlaylist || {};
(function ($, il) {
  il.VideoPlaylist = (function ($) {
    const t = il.VideoPlaylist;
    t.playlist = [];
    t.current_item = [];

    /**
     * Render single item of preview list
     * @param $wrap
     * @param list
     * @param item
     */
    const renderItem = (list_wrapper, $wrap, list, item, i, front) => {
      let { tpl } = list;
      const { id } = item;

      /* if (item.mime === "video/vimeo") {
        var video_id = "75754881";
        $.ajax({
          type:'GET',
          url: 'http://vimeo.com/api/v2/video/' + video_id + '.json',
          jsonp: 'callback',
          dataType: 'jsonp',
          success: function(data){
            console.log(data);
            var $item = $("#med_" + id);
            $item.find("[data-elementtype='title']").html(data[0].title);
            t.playlist[list_wrapper].items[i].title = data[0].title;
            $item.find("[data-elementtype='description']").html(data[0].description);
            t.playlist[list_wrapper].items[i].description = data[0].description;
            t.playlist[list_wrapper].items[i].duration = data[0].duration;
            $item.find("[data-elementtype='preview'] img").attr("src", data[0].thumbnail_large);
            if (t.current_item[t.playlist[list_wrapper].player_wrapper] === id) {
              il.VideoWidget.setMeta(t.playlist[list_wrapper].player_wrapper,
                data[0].title,
                data[0].description);
            }
          }
        });
      } */
      // preview_pic
      // $tpl.find("[data-elementtype='title']").html(item.linked_title);
      // $tpl.find("[data-elementtype='description']").html(item.description);
      // $tpl.find("[data-elementtype='preview']").html(item.preview);

      tpl = tpl.replace('#video-title#', item.linked_title);
      tpl = tpl.replace('#description#', item.description);
      tpl = tpl.replace('#img-src#', item.preview_pic);
      tpl = tpl.replace('#img-alt#', item.title);
      $tpl = $(tpl);
      $tpl.attr('id', `med_${id}`);
      $tpl.on('click', () => {
        il.VideoPlaylist.loadItem(list_wrapper, id, true);
      });
      if (item.completed) {
        $tpl.addClass('mcst-completed-preview');
      }
      if (front) {
        $wrap.prepend($tpl);
      } else {
        $wrap.append($tpl);
      }
    };

    /**
     * Render preview list
     * @param list_wrapper
     */
    const render = (list_wrapper) => {
      const $wrap = $(`#${list_wrapper}`);
      const list = t.playlist[list_wrapper];
      $wrap.html(' ');

      // render items
      cnt = 0;
      found = false;
      list.items.forEach((item, i, a) => {
        if (item.completed === false) {
          found = true;
        }
        if (found) {
          if (cnt < list.limit) {
            renderItem(list_wrapper, $wrap, list, item, i);
            t.playlist[list_wrapper].items[i].hidden = false;
            cnt++;
          } else {
            t.playlist[list_wrapper].items[i].hidden = true;
          }
        } else {
          t.playlist[list_wrapper].items[i].hidden = true;
        }
      });

      if (!found) {
        cnt = 0;
        let first = 0;
        list.items.forEach((item, i, a) => {
          if (cnt < list.limit) {
            if (first === 0) {
              first = item.id;
            }
            renderItem(list_wrapper, $wrap, list, item, i);
            t.playlist[list_wrapper].items[i].hidden = false;
            cnt++;
          } else {
            t.playlist[list_wrapper].items[i].hidden = true;
          }
        });
        if (first > 0) {
          loadItem(list_wrapper, first);
        }
      }

      refreshNavigation(list_wrapper);
    };

    /**
     * Show next items
     * @param list_wrapper
     */
    const nextItems = (list_wrapper) => {
      const $wrap = $(`#${list_wrapper}`);
      const list = t.playlist[list_wrapper];

      // render items
      cnt = 5;
      found = 0;
      list.items.forEach((item, i, a) => {
        if (item.hidden === false) {
          found = true;
        }
        if (found && item.hidden === true && cnt-- > 0) {
          renderItem(list_wrapper, $wrap, list, item, i);
          t.playlist[list_wrapper].items[i].hidden = false;
        }
      });
      refreshNavigation(list_wrapper);
    };

    /**
     * Show previous items
     * @param list_wrapper
     */
    const previousItems = (list_wrapper) => {
      const $wrap = $(`#${list_wrapper}`);
      const list = t.playlist[list_wrapper];
      const hiddenCompleted = [];
      found = 0;
      list.items.forEach((item, i, a) => {
        if (item.hidden === false) {
          found = true;
        }
        if (!found && item.hidden === true) {
          hiddenCompleted.push(item);
        }
      });
      let cnt = 0;
      hiddenCompleted.reverse().forEach((item, i, a) => {
        if (cnt++ < 5) {
          renderItem(list_wrapper, $wrap, list, item, i, true);
          t.playlist[list_wrapper].items[i].hidden = false;
        }
      });
      refreshNavigation(list_wrapper);
    };

    const progress_cb = (player_wrapper, current_time, duration, ended) => {
      let perc = 0;
      if (current_time > 0 && duration && duration > 0) {
        perc = 100 / duration * current_time;
        // console.log(perc);
      }
      for (const list_wrapper in t.playlist) {
        if (t.playlist.hasOwnProperty(list_wrapper) && t.playlist[list_wrapper].player_wrapper === player_wrapper) {
          const current = t.current_item[t.playlist[list_wrapper].player_wrapper];
          t.playlist[list_wrapper].items.forEach((v, i, a) => {
            if (v.id === current) {
              if (current_time > 0 && duration && duration > 0) {
                perc = 100 / duration * current_time;

                if (t.playlist[list_wrapper].completed_cb !== '') {
                  if (perc >= t.playlist[list_wrapper].percentage) {
                    if (!t.playlist[list_wrapper].items[i].completed) {
                      t.playlist[list_wrapper].items[i].completed = true;
                      console.log('sending completed cb');
                      $.ajax({
                        type: 'GET',
                        url: `${t.playlist[list_wrapper].completed_cb}&mob_id=${v.id}`,
                      });
                    }
                  }
                }
              }
            }
          });
        }
      }
    };

    const refreshNavigation = (list_wrapper) => {
      const current = t.current_item[t.playlist[list_wrapper].player_wrapper];

      let first = true;
      let has_previous_items = false;
      let has_next_items = false;
      let has_previous = false;
      let has_next = false;

      t.playlist[list_wrapper].items.forEach((v, i, a) => {
        if (first) {
          if (v.hidden === true) {
            has_previous_items = true;
          }
          if (current !== v.id) {
            has_previous = true;
          }
        }
        has_next_items = v.hidden;
        has_next = (v.id !== current);

        $(".il-mcst-videocast *[data-elementtype='nav'] button:first").attr('disabled', !has_previous);
        $(".il-mcst-videocast *[data-elementtype='nav'] button:last").attr('disabled', !has_next);
        $('#mcst-prev-items button').css('display', (has_previous_items ? '' : 'none'));
        $('#mcst-next-items button').css('display', (has_next_items ? '' : 'none'));

        $('#mcst_playlist > div.mcst-current').removeClass('mcst-current');
        $(`#med_${current}`).addClass('mcst-current');

        first = false;
      });

      t.playlist[list_wrapper].items.forEach((v, i, a) => {
        if (v.completed && v.id !== current) {
          $(`#med_${v.id}`).addClass('mcst-completed-preview');
        } else {
          $(`#med_${v.id}`).removeClass('mcst-completed-preview');
        }
        if (v.id === current) {
          const dd = document.querySelector('.ilToolbarStickyItem .dropdown button');
          if (dd) {
            dd.innerHTML = `<span style='vertical-align: bottom; max-width:60px; display: inline-block; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;'>${v.title}</span> <span class='caret'></span>`;
          }
        }
      });
    };

    /**
     * Load item from playlist
     * @param list_wrapper
     * @param id
     * @param play
     */
    const loadItem = (list_wrapper, id, play) => {
      const current = t.current_item[t.playlist[list_wrapper].player_wrapper];
      t.playlist[list_wrapper].items.forEach((v, i, a) => {
        if (v.id === id && id !== current) {
          if (v.hidden === true) {
            nextItems(list_wrapper);
          }
          il.VideoWidget.loadFile(t.playlist[list_wrapper].player_wrapper, v, play, progress_cb);
          t.current_item[t.playlist[list_wrapper].player_wrapper] = id;
          loadComments(id);
        }
      });
      refreshNavigation(list_wrapper);
    };

    const toggleItem = (listWrapper, id) => {
      const current = t.current_item[t.playlist[listWrapper].player_wrapper];
      if (current !== id) {
        loadItem(listWrapper, id, true);
      }
    };

    /**
     * Load item from playlist
     * @param list_wrapper
     * @param play
     */
    const loadFirst = (list_wrapper, play) => {
      let first = 0;
      t.playlist[list_wrapper].items.forEach((item, i, a) => {
        if (first === 0 && item.completed === false) {
          first = item.id;
        }
      });
      if (first > 0) {
        loadItem(list_wrapper, first, play);
      }
    };

    const previous = (list_wrapper) => {
      const current = t.current_item[t.playlist[list_wrapper].player_wrapper];
      t.playlist[list_wrapper].items.forEach((v, i, a) => {
        if (v.id === current) {
          if (t.playlist[list_wrapper].items[i - 1]) {
            loadItem(list_wrapper, t.playlist[list_wrapper].items[i - 1].id, true);
          }
        }
      });
    };

    const next = (list_wrapper) => {
      const current = t.current_item[t.playlist[list_wrapper].player_wrapper];
      t.playlist[list_wrapper].items.forEach((v, i, a) => {
        if (v.id === current) {
          if (t.playlist[list_wrapper].items[i + 1]) {
            loadItem(list_wrapper, t.playlist[list_wrapper].items[i + 1].id, true);
          }
        }
      });
    };

    /**
     * Init playlist
     * @param list_wrapper
     * @param player_wrapper
     * @param items
     * @param tpl
     * @param autoplay
     * @param limit
     * @param completed_cb
     * @param percentage
     */
    const init = function (list_wrapper, player_wrapper, items, tpl, autoplay, limit, completed_cb, autoplay_cb, percentage) {
      t.playlist[list_wrapper] = {
        player_wrapper,
        items,
        limit,
        tpl,
        autoplay,
        completed_cb,
        autoplay_cb,
        percentage,
      };

      il.VideoWidget.setPreviousCallback(player_wrapper, () => {
        previous(list_wrapper);
      });

      il.VideoWidget.setNextCallback(player_wrapper, () => {
        next(list_wrapper);
      });

      render(list_wrapper);
      loadFirst(list_wrapper, false);
    };


    const loadComments = (id) => {
      const el = document.querySelector('[data-mcst-comments]');
      if (el) {
        const url = `${el.dataset.mcstComments}&item_id=${id}`;
        $.ajax({
          url,
        }).done((data) => {
          $(el).html(data);
        });
      }
    };

    return {
      init,
      loadItem,
      toggleItem,
      nextItems,
      previousItems,
    };
  }($));
}($, il));
