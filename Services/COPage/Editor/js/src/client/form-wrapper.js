/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

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
    const form = document.createElement('form');
    form.method = "post";
    form.action = url;
    for (const [key, value] of Object.entries(params)) {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = key;
      input.value = value;
      form.appendChild(input);
    }
    document.body.appendChild(form);
    form.submit();
  }

}