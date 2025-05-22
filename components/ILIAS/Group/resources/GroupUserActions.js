/* eslint-disable */
il = il || {};
il.Group = il.Group || {};

(function ($, il) {
  il.Group.UserActions = (function ($, il) {
    let public_interface;

    // public interface
    public_interface = {

      initCreationForm(event, url) {
        event.preventDefault();
        event.stopPropagation();
        il.repository.core.fetchHtml(url).then((html) => {
          const modalContent = document.getElementById('il_grp_action_modal_content');
          il.repository.core.setInnerHTML(modalContent, html);
          il.Group.UserActions.setCreationSubmit();
        });
      },

      setCreationSubmit() {
        $('#il_grp_action_modal_content form').on('submit', (e) => {
          e.preventDefault();
          const form = document.querySelector('#il_grp_action_modal_content form');
          const formData = new FormData(form);
          const data = {};
          formData.forEach((value, key) => (data[key] = value));
          il.repository.core.fetchHtml(form.action, data, true).then((o) => {
            const contentEl = document.getElementById('il_grp_action_modal_content');
            il.repository.core.setInnerHTML(contentEl, o);
            il.Group.UserActions.setCreationSubmit();
          });
        });
      },

      createGroup(e) {
        e.preventDefault();
        const form = document.querySelector('#il_grp_action_modal_content form');
        const formData = new FormData(form);
        const data = {};
        formData.forEach((value, key) => (data[key] = value));
        il.repository.core.fetchHtml(form.action, data, true).then((o) => {
          const contentEl = document.getElementById('il_grp_action_modal_content');
          il.repository.core.setInnerHTML(contentEl, o);
        });
      },

      closeModal() {
        $('#il_grp_action_modal_content').closest('.il-modal-roundtrip').find('button.close').click();
      },
    };

    return public_interface;
  }($, il));

  // on ready initialisation
  $(() => {
    function initEvents(id) {
      $(id).find("[data-grp-action-add-to='1']").each(function () {
        $(this).on('click', function (e) {
          let url;
          e.preventDefault();
          url = $(this).data('url');

          if ($('#il_grp_action_modal_content').length) {
            url = `${url}&modal_exists=1`;
          } else {
            url = `${url}&modal_exists=0`;
          }
          il.repository.core.fetchHtml(url).then((html) => {
            const modalContent = document.getElementById('il_grp_action_modal_content');
            if (modalContent) {
              il.repository.core.setInnerHTML(modalContent, html);
              modalContent.closest('.il-modal-roundtrip').showModal();
            } else {
              $('body').append(html);
            }
          });
        });
      });
    }

    $(document).on('il.user.actions.updated', (ev, id) => {
      initEvents(`#${id}`);
    });
    // otherwise data attributes might not have been set yet
    setTimeout(() => {
      initEvents('body');
    }, 500);
  });
}($, il));
