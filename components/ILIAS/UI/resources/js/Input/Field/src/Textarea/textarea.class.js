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
 */

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
export default class Textarea {
  /**
     * @type {HTMLTextAreaElement}
     */
  textarea;

  /**
     * @type {HTMLSpanElement|null}
     */
  remainder = null;

  /**
     * @param {string} input_id
     * @throws {Error} if DOM elements are missing.
     */
  constructor(input_id) {
    this.textarea = document.getElementById(input_id);

    if (this.textarea === null) {
      throw new Error(`Could not find textarea for input-id '${input_id}'.`);
    }

    if (this.shouldShowRemainder()) {
      this.remainder = this.textarea.parentNode.querySelector('[data-action="remainder"]');
      if (!this.remainder instanceof HTMLSpanElement) {
        throw new Error(`Could not find remainder-element for input-id '${input_id}'.`);
      }

      this.textarea.addEventListener('input', () => {
        this.updateRemainderCountHook();
      });
    }
  }

  /**
     * @return {void}
     */
  updateRemainderCountHook() {
    if (this.shouldShowRemainder() && this.remainder !== null) {
      this.remainder.innerHTML = (this.textarea.maxLength - this.textarea.value.length).toString();
    }
  }

  /**
     * @param {string} content
     * @param {number|null} selection_start
     * @param {number|null} selection_end
     * @return {void}
     */
  updateTextareaContent(content, selection_start = null, selection_end = null) {
    // don't update content if the textarea is disabled
    if (this.isDisabled()) {
      return;
    }

    // only refocus the textarea if the content exceeds the max-limit.
    if (this.isContentTooLarge(content)) {
      this.updateRemainderCountHook();
      this.textarea.focus();
      return;
    }

    selection_start = selection_start ?? this.textarea.selectionStart;
    selection_end = selection_end ?? this.textarea.selectionEnd;

    this.textarea.value = content;

    if (selection_start < content.length) {
      this.textarea.selectionStart = selection_start;
    }

    if (selection_end < content.length) {
      this.textarea.selectionEnd = selection_end;
    }

    this.updateRemainderCountHook();
    this.textarea.focus();
  }

  /**
     * Returns the smaller value of the current selection-start or -end position.
     *
     * @return {number}
     */
  getAbsoluteSelectionStart() {
    return (this.textarea.selectionStart < this.textarea.selectionEnd)
      ? this.textarea.selectionStart
      : this.textarea.selectionEnd;
  }

  /**
     * Returns the bigger value of the current selection-start or -end position.
     *
     * @return {number}
     */
  getAbsoluteSelectionEnd() {
    return (this.textarea.selectionStart > this.textarea.selectionEnd)
      ? this.textarea.selectionStart
      : this.textarea.selectionEnd;
  }

  /**
     * @return {string[]}
     */
  getLinesBeforeSelection() {
    return getLinesOf(this.textarea.value).slice(0, getLineCountOf(this.getTextBeforeSelection()));
  }

  /**
     * @return {string[]}
     */
  getLinesAfterSelection() {
    const lines_of_content = getLinesOf(this.textarea.value);

    return lines_of_content.slice(
      getLineCountOf(this.getTextBeforeSelection() + this.getTextOfSelection()) + 1,
      lines_of_content.length,
    );
  }

  /**
     * @return {string[]}
     */
  getLinesOfSelection() {
    const lines_of_content = getLinesOf(this.textarea.value);

    return lines_of_content.slice(
      this.getLinesBeforeSelection().length,
      lines_of_content.length - this.getLinesAfterSelection().length,
    );
  }

  /**
     * @param {string} content
     * @return {boolean}
     */
  isContentTooLarge(content) {
    const max_limit = this.getMaxLength();

    // content is never too large if there's no max-limit.
    if (max_limit < 0) {
      return false;
    }

    return (max_limit < content.length);
  }

  /**
     * @return {string}
     */
  getTextBeforeSelection() {
    return this.textarea.value.substring(0, this.getAbsoluteSelectionStart());
  }

  /**
     * @return {string}
     */
  getTextAfterSelection() {
    return this.textarea.value.substring(this.getAbsoluteSelectionEnd(), this.textarea.value.length);
  }

  /**
     * @return {string}
     */
  getTextOfSelection() {
    return this.textarea.value.substring(this.getAbsoluteSelectionStart(), this.getAbsoluteSelectionEnd());
  }

  /**
     * @return {boolean}
     */
  isMultilineTextSelected() {
    return this.getTextOfSelection().includes('\n');
  }

  /**
     * @return {boolean}
     */
  isTextSelected() {
    return (this.textarea.selectionStart !== this.textarea.selectionEnd);
  }

  /**
     * @return {boolean}
     */
  shouldShowRemainder() {
    return (this.getMaxLength() > 0);
  }

  /**
     * This method exists due to a jsdom bug which returns the wrong default value.
     * This workaround has been adopted from:
     * @see https://github.com/jsdom/jsdom/issues/2927
     * @return {number}
     */
  getMaxLength() {
    return Number(this.textarea.getAttribute('maxlength') ?? -1);
  }

  /**
     * @return {boolean}
     */
  isDisabled() {
    return this.textarea.disabled;
  }
}

/**
 * @param {string} text
 * @return {number}
 */
function getLineCountOf(text) {
  return (text.match(/\n/g) ?? []).length;
}

/**
 * @param {string} text
 * @return {string[]}
 */
function getLinesOf(text) {
  return text.split(/\n/);
}
