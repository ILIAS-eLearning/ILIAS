il = il || {};
il.VideoWidget = il.VideoWidget || {};
(function ($, il) {
  il.VideoWidget = (function ($) {

    let t = il.VideoWidget;

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
        window.addEventListener("orientationchange", function() {
          window.setTimeout(function(){
            window.dispatchEvent(new Event('resize'));
          }, 1);
        }, false);

      });
    }
    _boot();

    const progress = () => {
      console.log("monitoring progress");
      // for all wrappers
      t.wrapper_ids.forEach(function(wrapper_id, i, a) {
        // get player
        let p = t.widget[wrapper_id].player
        // if the wrapper defines a progress callback, call it
        if (t.widget[wrapper_id].progress_cb) {
          console.log(p);
          // get current time, duration and ended information to callback
          t.widget[wrapper_id].progress_cb(wrapper_id, p.getCurrentTime(), p.node.duration, p.node.ended);
        }
      });
      setTimeout(progress, 1000);
    };

    // init player
    const init = (wrapper_id, tpl) => {
      t.widget[wrapper_id] = {
        tpl: tpl
      };
      if (!t.progress_running) {
        progress();
        t.progress_running = true;
      }
      t.wrapper_ids.push(wrapper_id);
    };

    const setMeta = (wrapper_id, title, description) => {
      let $wrap = $("#" + wrapper_id);
      $wrap.parent().find("[data-elementtype='title']").html(title);
      if (description !== "") {
        $wrap.parent().find("[data-elementtype='description']").html(description);
        $wrap.parent().find("[data-elementtype='description-wrapper']").removeClass("ilNoDisplay");
        //$wrap.parent().find("[data-elementtype='description']").addClass("ilNoDisplay");
        $wrap.parent().find("[data-elementtype='description-trigger']").removeClass("ilNoDisplay");
      } else {
        $wrap.parent().find("[data-elementtype='description']").html("");
        //$wrap.parent().find("[data-elementtype='description-wrapper']").addClass("ilNoDisplay");
      }
    };

    // load file into player and show it
    const loadFile = (wrapper_id, video_data, play, progress_cb) => {
      console.log("-----loadFile");
      console.log(wrapper_id);
      let content = t.widget[wrapper_id].tpl,
        $wrap = $("#" + wrapper_id);
      $wrap.html(
        content
      );
      const video_el = $("#" + wrapper_id + " video");
console.log(video_el);
      // https://github.com/vimeo/player.js/issues/197
      // add ?controls=0 for vimeo
      video_el.attr("src", video_data.resource);
      video_el.attr("type", video_data.mime);
      video_el.attr("poster", video_data.poster);

      setMeta(wrapper_id, video_data.title, video_data.description);

      video_el.mediaelementplayer({
        videoWidth: '100%',
        videoHeight: '100%',
        alwaysShowControls: true,
        success: function (mediaElement, originalNode, player) {
          if (play) {
            promise = player.play();
            if (promise !== undefined) {
              promise.then(_ => {
                // Autoplay started!
              }).catch(error => {
                console.log("Autostart was prevented by browser.");
              });
            }
          }
          t.widget[wrapper_id].player = player;
          t.widget[wrapper_id].progress_cb = progress_cb;
          player.node.addEventListener('ended', function(){
            progress_cb(wrapper_id, player.node.duration, player.node.duration, true);
          });
        }
      });
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
        console.log("calling next callback");
        console.log(t.widget[wrapper_id].next);
        t.widget[wrapper_id].next();
      }
    };

    return {
      init: init,
      loadFile: loadFile,
      setMeta: setMeta,
      setPreviousCallback: setPreviousCallback,
      setNextCallback: setNextCallback,
      previous: previous,
      next: next
    };
  })($);
})($, il);

il.VideoPlaylist = il.VideoPlaylist || {};
(function ($, il) {
  il.VideoPlaylist = (function ($) {

    let t = il.VideoPlaylist;
    t.playlist = [];
    t.current_item = [];


    /**
     * Render single item of preview list
     * @param $wrap
     * @param list
     * @param item
     */
    const renderItem = (list_wrapper, $wrap, list, item, i, front) => {
      let tpl = list.tpl,
        id = item.id;

      /*if (item.mime === "video/vimeo") {
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
      }*/
      console.log("---item---");
      console.log(item);
      // preview_pic
      //$tpl.find("[data-elementtype='title']").html(item.linked_title);
      //$tpl.find("[data-elementtype='description']").html(item.description);
      //$tpl.find("[data-elementtype='preview']").html(item.preview);

      tpl = tpl.replace("#video-title#", item.title);
      tpl = tpl.replace("#description#", item.description);
      tpl = tpl.replace("#img-src#", item.preview_pic);
      tpl = tpl.replace("#img-alt#", item.title);
      $tpl = $(tpl);
      $tpl.attr("id", "med_" + id);
      $tpl.on("click", () => {
          il.VideoPlaylist.loadItem(list_wrapper, id, true);
      });
      if (item.completed) {
        $tpl.addClass("mcst-completed-preview");
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
      let $wrap = $("#" + list_wrapper),
          list = t.playlist[list_wrapper];
      $wrap.html(" ");

      // render items
      cnt = 0;
      found = false;
      list.items.forEach(function (item, i, a) {
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
        list.items.forEach(function (item, i, a) {
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
      let $wrap = $("#" + list_wrapper),
        list = t.playlist[list_wrapper];

      // render items
      cnt = 5;
      found = 0;
      list.items.forEach(function (item, i, a) {
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
      let $wrap = $("#" + list_wrapper),
        list = t.playlist[list_wrapper],
        hiddenCompleted = [];
      found = 0;
      list.items.forEach(function (item, i, a) {
        if (item.hidden === false) {
          found = true;
        }
        if (!found && item.hidden === true) {
          hiddenCompleted.push(item);
        }
      });
      let cnt = 0;
      hiddenCompleted.reverse().forEach(function (item, i, a) {
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
        console.log(perc);
      }

      for (let list_wrapper in t.playlist) {
        if (t.playlist.hasOwnProperty(list_wrapper) && t.playlist[list_wrapper].player_wrapper === player_wrapper) {
          const current = t.current_item[t.playlist[list_wrapper].player_wrapper];
          t.playlist[list_wrapper].items.forEach(function (v, i, a) {
            if (v.id === current) {
              if (["video/vimeo", "video/youtube"].includes(v.mime)) {
                duration = v.duration;
              }

              if (current_time > 0 && duration && duration > 0) {
                perc = 100 / duration * current_time;
                if (t.playlist[list_wrapper].completed_cb !== '') {
                  if (perc > t.playlist[list_wrapper].percentage) {
                    t.playlist[list_wrapper].items[i].completed = true;
                    $.ajax({
                      type:'GET',
                      url: t.playlist[list_wrapper].completed_cb + '&mob_id=' + v.id
                    });
                  }
                }
              }

              // check if we should play the next item
              if (t.playlist[list_wrapper].autoplay) {
                if (ended || (v.mime === "video/vimeo" && v.duration <= Math.ceil(current_time))) {
                  autoplayNext(list_wrapper);
                }
              }
            }
          });
        }
      }

      console.log("duration " + duration);
      console.log("current time " + current_time);
      console.log("ended " + ended);
    };

    const refreshNavigation = (list_wrapper) => {
      const current = t.current_item[t.playlist[list_wrapper].player_wrapper];

      let first = true,
        has_previous_items = false,
        has_next_items = false,
        has_previous = false,
        has_next = false;

      t.playlist[list_wrapper].items.forEach(function (v, i, a) {
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

        $(".il-mcst-videocast *[data-elementtype='nav'] button:first").attr("disabled", !has_previous);
        $(".il-mcst-videocast *[data-elementtype='nav'] button:last").attr("disabled", !has_next);
        $("#mcst-prev-items button").css("display", (has_previous_items ? "" : "none"));
        $("#mcst-next-items button").css("display", (has_next_items ? "" : "none"));

        $("#mcst_playlist > div.mcst-current").removeClass("mcst-current");
        $("#med_" + current).addClass("mcst-current");

        first = false;
      });

      t.playlist[list_wrapper].items.forEach(function (v, i, a) {
        if (v.completed && v.id !== current) {
          $("#med_" + v.id).addClass("mcst-completed-preview");
        } else {
          $("#med_" + v.id).removeClass("mcst-completed-preview");
        }
      });

    }

    /**
     * @param list_wrapper
     */
    const autoplayNext = (list_wrapper) => {
      const current = t.current_item[t.playlist[list_wrapper].player_wrapper];
      let found = false,
        nextItem = 0;

      t.playlist[list_wrapper].items.forEach(function (v, i, a) {
        if (nextItem === 0 && found) {
          if (v.hidden === true) {
            nextItems(list_wrapper);
          }
          nextItem = v.id;
        }
        if (v.id === current) {
          found = true;
        }
      });
      if (nextItem > 0) {
        loadItem(list_wrapper, nextItem, true);
      }
      console.log("auto play next" + list_wrapper);
    }

    /**
     * Load item from playlist
     * @param list_wrapper
     * @param id
     * @param play
     */
    const loadItem = (list_wrapper, id, play) => {
      const current = t.current_item[t.playlist[list_wrapper].player_wrapper];
      t.playlist[list_wrapper].items.forEach(function (v, i, a) {


        if (v.id === id && id !== current) {
          if (v.hidden === true) {
            nextItems(list_wrapper);
          }
          il.VideoWidget.loadFile(t.playlist[list_wrapper].player_wrapper, v, play, progress_cb);
          t.current_item[t.playlist[list_wrapper].player_wrapper] = id;
        }
      });
      refreshNavigation(list_wrapper);
    };

    /**
     * Load item from playlist
     * @param list_wrapper
     * @param play
     */
    const loadFirst = (list_wrapper, play) => {
      let first = 0;
      t.playlist[list_wrapper].items.forEach(function (item, i, a) {
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
      t.playlist[list_wrapper].items.forEach(function (v, i, a) {
        if (v.id === current) {
          if (t.playlist[list_wrapper].items[i-1]) {
            loadItem(list_wrapper, t.playlist[list_wrapper].items[i-1].id, true);
          }
        }
      });
    };

    const next = (list_wrapper) => {
      const current = t.current_item[t.playlist[list_wrapper].player_wrapper];
      t.playlist[list_wrapper].items.forEach(function (v, i, a) {
        if (v.id === current) {
          if (t.playlist[list_wrapper].items[i+1]) {
            loadItem(list_wrapper, t.playlist[list_wrapper].items[i+1].id, true);
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
        player_wrapper: player_wrapper,
        items: items,
        limit: limit,
        tpl: tpl,
        autoplay: autoplay,
        completed_cb: completed_cb,
        autoplay_cb: autoplay_cb,
        percentage: percentage
      };

      il.VideoWidget.setPreviousCallback(player_wrapper, () => {
        previous(list_wrapper);
      });

      il.VideoWidget.setNextCallback(player_wrapper, () => {
        console.log("in next callback " + list_wrapper);
        next(list_wrapper);
      });

      //console.log(items);
      //console.log(autoplay);
      render(list_wrapper);
      loadFirst(list_wrapper, false);
    };

    const autoplay = (list_wrapper, active) => {
      t.playlist[list_wrapper].autoplay = active;
      $.ajax({
        type:'GET',
        url: t.playlist[list_wrapper].autoplay_cb + '&autoplay=' + (active ? "1" : "0")
      });
    };

    return {
      init: init,
      loadItem: loadItem,
      autoplay: autoplay,
      nextItems: nextItems,
      previousItems: previousItems
    };
  })($);
})($, il);