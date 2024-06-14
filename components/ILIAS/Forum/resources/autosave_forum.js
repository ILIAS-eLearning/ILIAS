(function (root, scope, factory) {
  scope.ForumDraftsAutosave = factory(root, root.jQuery);
}(window, il, (root, $) => {
  const pub = {}; const pro = {}; let draft_as_handle = null; let
    autosave_active = true;

  pub.disableAutosave = function () {
    autosave_active = false;
  };

  pub.enableAutosave = function () {
    autosave_active = true;
  };

  pub.init = function (options) {
    const settings = $.extend({
      interval: 1000 * 10,
      url: '',
      loading_img_src: '',
      draft_id: 0,
      selectors: {
        form: '',
      },
    }, options); const
      { draft_id } = settings;

    const $form = $(settings.selectors.form);

    const saveDraftCallback = function saveDraftCallback() {
      if (typeof tinyMCE !== 'undefined') {
        if (tinyMCE) tinyMCE.triggerSave();
      }

      if (autosave_active && $('#subject').val() != '' && $('#message').val() != '') 	{
        const data = $form.serialize();

        $form.find('.ilFrmLoadingImg').remove();
        $form.find('input[type=submit]').attr('disabled', 'disabled');
        $form.find('.ilFormCmds').each(function () {
          $(`<img class="ilFrmLoadingImg" src="${settings.loading_img_src}" />`)
            .css('paddingRight', '10px')
            .insertBefore($(this).find('input[type=submit]:first'));
        });
        $('#ilsaving').removeClass('ilNoDisplay');

        il.ForumDraftsAutosave.disableAutosave();
        $.ajax({
          type: 'POST',
          url: settings.url,
          data,
          dataType: 'json',
          success(response) {
            $form.find('input[type=submit]').attr('disabled', false);
            $form.find('.ilFrmLoadingImg').remove();
            $('#ilsaving').addClass('ilNoDisplay');

            if (typeof response.draft_id !== 'undefined' && response.draft_id > 0) {
              $draft_id.val(response.draft_id);
            }

            il.ForumDraftsAutosave.enableAutosave();
          },
        });
      }
    };

    if ($('#ilsaving').size() === 0) {
      $(`<div id="ilsaving" class="ilHighlighted ilNoDisplay">${il.Language.txt('saving')}</div>`).appendTo($('body'));
    }
    $('#ilsaving').css('zIndex', 10000);
    var $draft_id = $form.find('#draft_id');
    if ($draft_id.size() === 0) {
      $draft_id = $('<input type="hidden" name="draft_id" id="draft_id" value="" />');
      $form.append($draft_id);
    }

    $(() => {
      draft_as_handle = root.setInterval(saveDraftCallback, settings.interval);

      $form.on('submit', () => {
        root.clearInterval(draft_as_handle);
      });
    });
  };

  return pub;
}));

il.Util.addOnLoad(function() {
  il.Util.addOnLoad(function() {
    const thread_history = document.querySelectorAll('.found_threat_history_to_restore');
    if (thread_history.length > 0) {
      const $modal = $('[data-modal-id="frm_autosave_restore"]');
      if ($modal) {
        il.ForumDraftsAutosave.disableAutosave();
        $modal.get(0).querySelectorAll('.modal-footer .btn-primary').forEach((primary_btn) => primary_btn.parentNode.removeChild(primary_btn));
        $modal.modal('show');
        $modal.on('hidden.bs.modal', () => {
          il.ForumDraftsAutosave.enableAutosave();
        });
      }
    }
  });
});