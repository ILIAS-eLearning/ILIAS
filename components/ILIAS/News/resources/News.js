il.News = {

  items: {},

  current_id: 0,

  ajax_url: '',

  requestRunning: false,

  scroll_init: false,

  init() {
    const t = il.News;
    $('#form_news_edit_form').closest('.il-modal-roundtrip').find('.modal-footer').hide();
    $('#news_btn_cancel_update').on('click', (e) => {
      e.preventDefault();
      $('.ilAdminRow .alert').remove();
      t.closeEditModal();
    });
    $('#news_btn_update').on('click', (e) => {
      const t = il.News;
      // e.preventDefault();
      t.save();
      t.closeEditModal();
    });
    t.moreOnScroll();
  },

  moreOnScroll() {
    const w = $('main'); const
      t = il.News;
    console.log(w);
    if (!t.scroll_init) {
      w.on('scroll', () => {
        const main = $('main');
        const sp = main.scrollTop(); // scroll position (starting with 0)
        const vh = main.height(); // visible height
        const th = main[0].scrollHeight; // total height
        if (sp + vh + 60 > th) {
          t.moreNews();
        }
      });
      t.scroll_init = true;
    }
  },

  startMoreRequest() {
    const t = il.News;
    if (t.requestRunning) {
      return false;
    }
    t.requestRunning = true;
    t.showLoader(true);

    return true;
  },

  stopMoreRequest() {
    const t = il.News;
    t.requestRunning = false;
    t.showLoader(false);
  },

  moreNews() {
    const t = il.News;
    if (!t.startMoreRequest()) {
      return;
    }
    // console.log("get more news");

    t.scroll_init = false;
    $(window).off('scroll');

    $.ajax({
      url: `${il.News.ajax_url}&cmd=loadMore`,
      type: 'POST',
      dataType: 'json',
      data: {
        rendered_news: $.map(il.News.items, (e) => e.id),
      },
    }).done((r) => {
      if (r.data !== undefined && r.data.html !== '') {
        t.appendNews(r);
        // il.News.addScrollToBottomListener();
        t.stopMoreRequest();
      } else {
        t.stopMoreRequest();
      }
    }).fail((e) => {
      t.stopMoreRequest();
    });
  },

  showLoader(s) {
    if (s) {
      $('.ilNewsTimelineMoreLoader').removeClass('ilHidden');
    } else {
      $('.ilNewsTimelineMoreLoader').addClass('ilHidden');
    }
  },

  appendNews(r) {
    const t = il.News;
    if (r.html == '') {
      return;
    }
    // console.log(r.data);
    for (const i in r.data) {
      t.items[i] = r.data[i];
    }
    $('ul.ilTimeline').append(r.html);

    $('.dynamic-height-active').removeClass('dynamic-height-active');
    $('.js-dynamic-show-hide').css('display', '').off('click');
    $('.dynamic-height-wrap').css('max-height', '');
    // $('.dynamic-max-height').dynamicMaxHeight();

    il.Timeline.compressEntries();
    il.MediaObjects.autoInitPlayers();
    t.moreOnScroll();
  },

  setAjaxUrl(url) {
    const t = il.News;

    t.ajax_url = url;
  },

  setItems(items) {
    const t = il.News;

    t.items = items;
  },

  create(keep_values) {
    const t = il.News;

    t.current_id = 0;

    $('#form_news_edit_form').closest('.il-modal-roundtrip').find('.modal-title').html(il.Language.txt('create'));
    $('#news_btn_update').attr('value', il.Language.txt('save'));
    if (!keep_values) {
      $('#news_title').val('');
      $('#news_content').val('');
      $('#news_content_long').val('');
      $('.help-block.alert').remove();
    }
    if (typeof tinyMCE !== 'undefined' && tinyMCE.get('news_content')) {
      tinyMCE.get('news_content').setContent('');
    }
    $('#form_news_edit_form').closest('.il-modal-roundtrip').find('input[name="media_delete"]').css('display', 'none');
    $('#form_news_edit_form').closest('.il-modal-roundtrip').find('label[for="media_delete"]').css('display', 'none');
    t.showEditModal();

    return false;
  },

  showEditModal() {
    const newsData = document.querySelector("[data-news-type='init']");
    if (newsData) {
      const signalId = newsData.dataset.newsEditModalSignal;
      $(document).trigger(
        signalId,
        {
          id: signalId,
          triggerer: $(this),
          options: JSON.parse('[]'),
        },
      );
    }
  },

  closeEditModal() {
    const newsData = document.querySelector("[data-news-type='init']");
    if (newsData) {
      const signalId = newsData.dataset.newsEditCloseSignal;
      $(document).trigger(
        signalId,
        {
          id: signalId,
          triggerer: $(this),
          options: JSON.parse('[]'),
        },
      );
    }
  },

  edit(id, keep_values) {
    const t = il.News;
    t.current_id = id;

    $('#form_news_edit_form').closest('.il-modal-roundtrip').find('.modal-title').html(il.Language.txt('edit'));
    $('#news_btn_update').attr('value', il.Language.txt('save'));
    if (!keep_values) {
      $('#news_title').val(t.items[id].title);
      $(`#news_visibility input[value='${t.items[id].visibility}']`).prop('checked', true);
      if (typeof tinyMCE !== 'undefined' && tinyMCE.get('news_content')) {
        tinyMCE.get('news_content').setContent(t.items[id].content);
      } else {
        $('#news_content').val(t.items[id].content);
      }
      $('.help-block.alert').remove();
    }

    if (t.items[id].mob_id > 0) {
      $('#form_news_edit_form').closest('.il-modal-roundtrip').find('input[name="media_delete"]').css('display', '');
      $('#form_news_edit_form').closest('.il-modal-roundtrip').find('label[for="media_delete"]').css('display', '');
      $('#form_news_edit_form').closest('.il-modal-roundtrip').find('input[name="media_delete"]').prop('checked', false);
    } else {
      $('#form_news_edit_form').closest('.il-modal-roundtrip').find('input[name="media_delete"]').css('display', 'none');
      $('#form_news_edit_form').closest('.il-modal-roundtrip').find('label[for="media_delete"]').css('display', 'none');
    }

    t.showEditModal();

    return false;
  },

  save() {
    const t = il.News; let cmd; let d; let
      content;

    if (typeof tinyMCE !== 'undefined' && tinyMCE.get('news_content')) {
      content = tinyMCE.get('news_content').getContent();
    } else {
      content = $('#news_content').val();
    }
    // data
    d = {
      news_title: $('#news_title').val(),
      news_visibility: $("#news_visibility input[type='radio']:checked").val(),
      news_content: content,
      news_content_long: '',
    };

    if (t.current_id > 0) {
      d.id = t.current_id;
      cmd = 'update';
    } else {
      cmd = 'save';
    }

    $('#id').val(d.id);
    $('#news_action').val(cmd);

    //	$("#form_news_edit_form").submit();

    return;

    // console.log(d); return;

    $.ajax({
      url: `${t.ajax_url}&cmd=${cmd}`,
      type: 'POST',
      data: d,
      success(data, s, j) {
        console.log(data); return false;
        window.location.href = `${t.ajax_url}&cmd=show`;
      },
      error(j, s, e) {
        window.location.href = `${t.ajax_url}&cmd=show`;
      },
    });
  },

  delete(id) {
    const t = il.News;
    t.current_id = id;

    $('#news_delete_news_title').html(t.items[id].title);

    const newsData = document.querySelector("[data-news-type='init']");
    if (newsData) {
      const signalId = newsData.dataset.newsDeleteModalSignal;
      $(document).trigger(
        signalId,
        {
          id: signalId,
          triggerer: $(this),
          options: JSON.parse('[]'),
        },
      );
    }

    return false;
  },

  remove() {
    const t = il.News; let cmd; let d; let
      content;

    cmd = 'remove';

    d = {
      id: t.current_id,
    };

    $.ajax({
      url: `${t.ajax_url}&cmd=${cmd}`,
      type: 'POST',
      data: d,
      success(data, s, j) {
        window.location.href = `${t.ajax_url}&cmd=show`;
      },
      error(j, s, e) {
        window.location.href = `${t.ajax_url}&cmd=show`;
      },
    });
  },

};

$(() => {
  il.News.init();
});
