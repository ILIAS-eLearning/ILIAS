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
 * HTML transformations
 */
export default class HTMLTransform {

  /**
   * @type {boolean}
   */
  //debug = true;

  constructor() {
    this.debug = true;
  }

  /**
   * @param {string} str
   * @return {string}
   */
  removeLineFeeds(str) {
    str = str.replace(/(\r\n|\r|\n)/g, '\n');
    str = str.replace(/(\n)/g, ' ');
    return str;
  }

  /**
   * @param {string} tag
   * @param {string} str
   * @return {string}
   */
  removeAttributesFromTag(tag, str) {
    const re = new RegExp("(<" + tag + " [^>]*>)","g");
    return str.replace(re, '<' + tag + '>');
  }

  /**
   * @param {string} tag
   * @param {string} str
   * @return {string}
   */
  removeTag(tag, str) {
    const re1 = new RegExp("(<" + tag + " [^>]*>)","g");
    const re2 = new RegExp("(<\/" + tag + ">)","g");
    str = str.replace(re1, '');
    str = str.replace(re2, '');
    return str;
  }

  /**
   * convert <p> tags to <br />
   * @param {string} c
   * @return {string}
   */
  p2br(c) {
    // remove <p> and \n
    c = c.split("<p>").join("");
    c = c.split("\n").join("");

    // convert </p> to <br />
    c = c.split("</p>").join("<br />");

    // remove trailing <br />
    if (c.substr(c.length - 6) === "<br />") {
      c = c.substr(0, c.length - 6);
    }

    return c;
  }


}