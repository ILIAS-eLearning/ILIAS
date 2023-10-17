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
 *********************************************************************/

/**
 * General ui utilities
 */
export default class Util {

  constructor() {
  }

  setInnerHTML(el, html) {
    el.innerHTML = html;

    Array.from(el.querySelectorAll("script"))
    .forEach( oldScriptEl => {
      const newScriptEl = document.createElement("script");

      Array.from(oldScriptEl.attributes).forEach( attr => {
        newScriptEl.setAttribute(attr.name, attr.value)
      });

      const scriptText = document.createTextNode(oldScriptEl.innerHTML);
      newScriptEl.appendChild(scriptText);

      oldScriptEl.parentNode.replaceChild(newScriptEl, oldScriptEl);
    });
  }

  sendFiles(form) {
    let input_id, dropzone, cnt = 0;
    return new Promise((resolve, reject) => {
      if (typeof Dropzone !== "undefined" && typeof Dropzone.instances !== "undefined" && Array.isArray(Dropzone.instances)) {
        for (let i = 0; i < Dropzone.instances.length; i++) {
          const el = Dropzone.instances[i].element;
          // process only dropzones in our form with file data
          if (form.contains(el) && Dropzone.instances[i].getQueuedFiles().length > 0) {
            cnt++;
            Dropzone.instances[i].on('queuecomplete', () => {
              cnt--;
              if (cnt === 0) {
                resolve();
              }
            });
            Dropzone.instances[i].processQueue();
          }
        }
      }
      if (cnt === 0) {
        resolve();
      }
    });
  }

  showModal(modalUiModel, title, content, button_txt = null, onclick= null) {

    $("#il-copg-ed-modal").remove();
    let modal_template = modalUiModel.template;
    modal_template = modal_template.replace("#title#", title);
    modal_template = modal_template.replace("#content#", content);
    modal_template = modal_template.replace("#button_title#", button_txt);

    $("body").append("<div id='il-copg-ed-modal'>" + modal_template + "</div>");

    $(document).trigger(
      modalUiModel.signal,
      {
        'id': modalUiModel.signal,
        'triggerer': $(this),
        'options': JSON.parse('[]')
      }
    );

    if (button_txt) {
      // use modal button, remove form buttons
      const b = document.querySelector("#il-copg-ed-modal .modal-footer button");
      b.addEventListener("click", onclick);
      document.querySelectorAll("#il-copg-ed-modal .il-standard-form-cmd").forEach((fc) => {
        fc.remove();
      });
    } else {
      // remove modal buttons
      document.querySelectorAll("#il-copg-ed-modal .modal-footer").forEach((b) => {
        b.remove();
      });
    }
  }

  hideCurrentModal() {
    $("#il-copg-ed-modal .modal").modal("hide");
  }

}
