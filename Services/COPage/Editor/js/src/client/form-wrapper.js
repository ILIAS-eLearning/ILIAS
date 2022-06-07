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
 * form wrapper
 *
 */
export default {

  /**
   * @param {string} url
   * @param {Object} params
   */
  postForm: function (url = '', params = {}) {

    const appendHidden = function (form, key, value) {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = key;
      input.value = value;
      form.appendChild(input);
    };


    const form = document.createElement('form');
    form.method = "post";
    form.action = url;
    for (const [key, value] of Object.entries(params)) {
      if (Array.isArray(value)) {
        value.forEach(v => appendHidden(form, key + '[]', v));
      } else {
        appendHidden(form, key, value);
      }
    }
    document.body.appendChild(form);
    //console.log(form);
    form.submit();
  }

}