import Textarea from '../Textarea/textarea.class';

/**
 * @type {string}
 */
const CONTENT_WRAPPER_KEY_TEXTAREA = 'textarea';

/**
 * @type {string}
 */
const CONTENT_WRAPPER_KEY_PREVIEW = 'preview';

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
export default class Markdown extends Textarea {
  /**
     * @type {string[]}
     */
  preview_history = [];

  /**
     * @type {PreviewRenderer}
     */
  preview_renderer;

  /**
     * @type {Map}
     */
  content_wrappers;

  /**
     * @type {HTMLButtonElement[]}
     */
  view_controls;

  /**
     * @type {HTMLButtonElement[]}
     */
  actions;

  /**
     * @param {PreviewRenderer} preview_renderer
     * @param {string} input_id
     * @throws {Error} if DOM elements are missing.
     */
  constructor(preview_renderer, input_id) {
    super(input_id);

    const input_wrapper = this.textarea.closest('.c-field-markdown');

    if (input_wrapper === null) {
      throw new Error(`Could not find input-wrapper for input-id '${input_id}'.`);
    }

    this.preview_renderer = preview_renderer;

    this.content_wrappers = getContentWrappersOrAbort(input_wrapper);
    this.view_controls = getViewControlsOrAbort(input_wrapper);
    this.actions = getMarkdownActions(input_wrapper);

    let has_newline_been_inserted = true;

    this.textarea.addEventListener('keydown', (event) => {
      has_newline_been_inserted = this.handleEnterKeyBeforeInsertionHook(event);
    });

    this.textarea.addEventListener('keyup', (event) => {
      this.handleEnterKeyAfterInsertionHook(event, has_newline_been_inserted);
    });

    this.actions.forEach((action) => {
      action.addEventListener('click', (event) => {
        this.performMarkdownActionHook(event);
      });
    });

    this.view_controls.forEach((control) => {
      control.addEventListener('click', () => {
        this.toggleViewingModeHook();
      });
    });
  }

  /**
     * Automatically inserts a bullet-point or enumeration on the newly added line
     * according to the previous one.
     *
     * NOTE that this hook should only fire if the previous hook has inserted a
     * newline, otherwise this would undo the previous action.
     *
     * @param {KeyboardEvent} event
     * @param {boolean} newline_inserted
     * @return {void}
     */
  handleEnterKeyAfterInsertionHook(event, newline_inserted) {
    // skip this hook if the previous one didn't insert a newline,
    // otherwise this hook would undo the previous action.
    if (!newline_inserted || !isEnterKeyPressed(event)) {
      return;
    }

    const previous_line = this.getLinesBeforeSelection().pop();

    if (undefined !== previous_line && isBulletPointed(previous_line)) {
      this.applyTransformationToSelection(toggleBulletPoints);
      return;
    }

    if (undefined !== previous_line && isEnumerated(previous_line)) {
      this.insertSingleEnumeration();
    }
  }

  /**
     * Removes the bullet-point or enumeration of the current line if there aren't
     * any other characters.
     *
     * @param {KeyboardEvent} event
     * @return {boolean}
     */
  handleEnterKeyBeforeInsertionHook(event) {
    if (!isEnterKeyPressed(event)) {
      return false;
    }

    const current_line = this.getLinesOfSelection().shift();

    // nothing to do if the current line is not an empty list entry.
    if (undefined === current_line || !isEmptyListEntry(current_line)) {
      return true;
    }

    let text_before_selection = this.getLinesBeforeSelection().join('\n');
    let text_after_selection = this.getLinesAfterSelection().join('\n');

    if (text_before_selection.length > 0) {
      text_before_selection += '\n';
    }

    if (text_after_selection.length > 0) {
      text_after_selection = `\n${text_after_selection}`;
    }

    this.updateTextareaContent(
      text_before_selection + text_after_selection,
      this.getAbsoluteSelectionStart() - current_line.length,
      this.getAbsoluteSelectionEnd() - current_line.length,
    );

    // prevent newline from being added.
    event.preventDefault();
    return false;
  }

  /**
     * @param {MouseEvent} event
     * @return {void}
     */
  performMarkdownActionHook(event) {
    const markdown_action = getMarkdownActionOfButton(event.target);

    switch (markdown_action) {
      case 'insert-heading':
        this.insertCharactersAroundSelection('# ', '');
        break;
      case 'insert-link':
        this.insertCharactersAroundSelection('[', '](url)');
        break;
      case 'insert-bold':
        this.insertCharactersAroundSelection('**', '**');
        break;
      case 'insert-italic':
        this.insertCharactersAroundSelection('_', '_');
        break;
      case 'insert-bullet-points':
        this.applyTransformationToSelection(toggleBulletPoints);
        break;
      case 'insert-enumeration':
        (this.isMultilineTextSelected())
          ? this.applyTransformationToSelection(toggleEnumeration)
          : this.insertSingleEnumeration();
        break;
      default:
        throw new Error(`Could not perform markdown-action '${markdown_action}'.`);
    }
  }

  /**
     * @return {void}
     */
  toggleViewingModeHook() {
    this.content_wrappers.forEach((wrapper) => {
      toggleClassOfElement(wrapper, 'hidden');
    });

    this.view_controls.forEach((control) => {
      toggleClassOfElement(control, 'engaged');
    });

    // only toggle actions if they weren't disabled initially.
    if (!this.isDisabled()) {
      this.actions.forEach((action) => {
        action.disabled = !action.disabled;
        const glyph = action.querySelector('.glyph');
        if (glyph !== null) {
          toggleClassOfElement(glyph, 'disabled');
        }
      });
    }

    this.maybeUpdatePreviewContent();
  }

  /**
     * Insert a new enumeration on the current line if it's not already one.
     * All lines after that will be reindexed as long as they continue the
     * current enumeration.
     *
     * @return {void}
     */
  insertSingleEnumeration() {
    const lines_of_selection = this.getLinesOfSelection();

    // abort (refocus) if the current selection is not a single line or
    // is already enumerated.
    if (lines_of_selection.length !== 1) {
      this.textarea.focus();
      return;
    }

    const lines_before_selection = this.getLinesBeforeSelection();
    const last_index = lines_before_selection.length - 1;
    let previous_number = (last_index >= 0) ? getFirstNumber(lines_before_selection[last_index]) ?? 0 : 0;

    const new_lines_of_selection = toggleEnumeration(lines_of_selection, ++previous_number);
    const lines_after_selection = reindexContinuousLinesOfEnumeration(this.getLinesAfterSelection(), previous_number);

    let text_before_selection = lines_before_selection.join('\n');
    const text_after_selection = lines_after_selection.join('\n');
    let text_of_selection = new_lines_of_selection.join('\n');

    if (text_before_selection.length > 0 && text_of_selection.length > 0) {
      text_before_selection += '\n';
    }

    if (text_of_selection.length > 0 && text_after_selection.length > 0) {
      text_of_selection += '\n';
    }

    const new_content = text_before_selection + text_of_selection + text_after_selection;
    const character_diff = new_content.length - this.textarea.value.length;

    // the selection should be shifted by the amount of newly added/removed
    // characters, so that the same text is still highlighted.
    this.updateTextareaContent(
      new_content,
      this.getAbsoluteSelectionStart() + character_diff,
      this.getAbsoluteSelectionEnd() + character_diff,
    );
  }

  /**
     * @param {function(string[]): string[]} transformation
     * @return {void}
     * @throws {Error} if the transformation does not return an array.
     *                 if the transformation is not a function.
     */
  applyTransformationToSelection(transformation) {
    if (!transformation instanceof Function) {
      throw new Error(`Transformation must be an instance of Function, ${typeof transformation} given.`);
    }

    const transformed_selection = transformation(this.getLinesOfSelection());

    if (!transformed_selection instanceof Array) {
      throw new Error(`Transformation must return an instance of Array, ${typeof transformed_selection} returned.`);
    }

    const is_multiline = (transformed_selection.length > 1);

    let text_before_selection = this.getLinesBeforeSelection().join('\n');
    const text_after_selection = this.getLinesAfterSelection().join('\n');
    let text_of_selection = transformed_selection.join('\n');

    if (text_before_selection.length > 0 && text_of_selection.length > 0) {
      text_before_selection += '\n';
    }

    if (text_of_selection.length > 0 && text_after_selection.length > 0) {
      text_of_selection += '\n';
    }

    const new_content = text_before_selection + text_of_selection + text_after_selection;
    const character_diff = new_content.length - this.textarea.value.length;

    // the new selection should hold all transformed lines if they're a
    // multiline selection. Otherwise, the selection should be shifted
    // by the amount of newly added/removed characters, so that the same
    // text is still highlighted.

    const new_selection_start = (is_multiline)
      ? text_before_selection.length
      : this.getAbsoluteSelectionStart() + character_diff;
    const new_selection_end = (is_multiline)
      ? new_selection_start + text_of_selection.length - 1
      : this.getAbsoluteSelectionEnd() + character_diff;
    this.updateTextareaContent(new_content, new_selection_start, new_selection_end);
  }

  /**
     * @param {string} chars_before_seletion
     * @param {string} chars_after_selection
     * @return {void}
     */
  insertCharactersAroundSelection(chars_before_seletion, chars_after_selection) {
    const new_content = this.getTextBeforeSelection()
            + chars_before_seletion
            + this.getTextOfSelection()
            + chars_after_selection
            + this.getTextAfterSelection();

    // selection must be moved by the length of chars inserted before the selection
    // in order to keep the same text highlighted.
    const new_selection_start = this.getAbsoluteSelectionStart() + chars_before_seletion.length;
    const new_selection_end = this.getAbsoluteSelectionEnd() + chars_before_seletion.length;

    this.updateTextareaContent(new_content, new_selection_start, new_selection_end);
  }

  /**
     * Updates the current preview if the previously rendered content has changed.
     *
     * @return {void}
     */
  maybeUpdatePreviewContent() {
    const previous_content = this.preview_history[(this.preview_history.length - 1)] ?? '';
    const current_content = this.textarea.value;

    if (current_content === previous_content) {
      return;
    }

    this.preview_history.push(current_content);
    this.preview_renderer
      .getPreviewHtmlOf(current_content).then((html) => {
        this.content_wrappers.get(CONTENT_WRAPPER_KEY_PREVIEW).innerHTML = html;
      });
  }

  /**
     * @return {function(string[]): string[]}
     */
  getBulletPointTransformation() {
    return toggleBulletPoints;
  }

  /**
     * @return {function(string[], number=1): string[]}
     */
  getEnumerationTransformation() {
    return toggleEnumeration;
  }
}

/**
 * @param {HTMLDivElement} input_wrapper
 * @return {Map}
 * @throws {Error}
 */
function getContentWrappersOrAbort(input_wrapper) {
  const content_wrappers = new Map();

  content_wrappers.set(CONTENT_WRAPPER_KEY_TEXTAREA, input_wrapper.querySelector('textarea'));
  content_wrappers.set(CONTENT_WRAPPER_KEY_PREVIEW, input_wrapper.querySelector('.c-field-markdown__preview'));

  content_wrappers.forEach((wrapper) => {
    if (wrapper === null) {
      throw new Error('Could not find all content-wrappers for markdown-input.');
    }
  });

  return content_wrappers;
}

/**
 * @param {HTMLDivElement} input_wrapper
 * @return {HTMLButtonElement[]}
 * @throws {Error}
 */
function getViewControlsOrAbort(input_wrapper) {
  const controls = input_wrapper
    .querySelector('.il-viewcontrol-mode')
    ?.getElementsByTagName('button');

  if (!controls instanceof HTMLCollection || controls.length !== 2) {
    throw new Error('Could not find exactly two view-controls.');
  }

  return [...controls];
}

/**
 * @param {HTMLDivElement} input_wrapper
 * @return {HTMLButtonElement[]}
 * @throws {Error}
 */
function getMarkdownActions(input_wrapper) {
  const actions = input_wrapper
    .querySelector('.c-field-markdown__actions')
    ?.getElementsByTagName('button');

  if (actions instanceof HTMLCollection) {
    return [...actions];
  }

  return [];
}

/**
 * @param {HTMLButtonElement} button
 * @return {string|null}
 */
function getMarkdownActionOfButton(button) {
  const action_wrapper = button.parentNode.closest('span');
  if (!action_wrapper instanceof HTMLSpanElement) {
    return null;
  }

  if (!action_wrapper.hasAttribute('data-action')) {
    return null;
  }

  return action_wrapper.dataset.action;
}

/**
 * @param {string[]} lines_after_selection
 * @param {number} previous_number
 * @return {string[]}
 */
function reindexContinuousLinesOfEnumeration(lines_after_selection, previous_number = 0) {
  if (lines_after_selection.length < 1) {
    return [];
  }

  const reindexed_lines = [];
  for (const line of lines_after_selection) {
    if (!isEnumerated(line)) {
      break;
    }

    reindexed_lines.push(line.replace(/([0-9]+)/, (++previous_number).toString()));
  }

  // replace all reindexed lines in the actual array of lines if necessary.
  if (reindexed_lines.length > 0) {
    lines_after_selection = reindexed_lines.concat(
      lines_after_selection.slice(reindexed_lines.length),
    );
  }

  return lines_after_selection;
}

/**
 * @param {string[]} lines_of_selection
 * @return {string[]}
 */
function toggleBulletPoints(lines_of_selection) {
  const transformed_lines = [];
  const to_list = !isBulletPointed(lines_of_selection[0] ?? '');
  for (const line of lines_of_selection) {
    transformed_lines.push(
      (to_list) ? `- ${line}` : removeBulletPointOrEnummeration(line),
    );
  }

  return transformed_lines;
}

/**
 * @param {string[]} lines_of_selection
 * @param {number} next_number
 * @return {string[]}
 */
function toggleEnumeration(lines_of_selection, next_number = 1) {
  const transformed_lines = [];
  const to_list = !isEnumerated(lines_of_selection[0] ?? '');
  for (const line of lines_of_selection) {
    transformed_lines.push(
      (to_list) ? `${next_number++}. ${line}` : removeBulletPointOrEnummeration(line),
    );
  }

  return transformed_lines;
}

/**
 * @param {string} line
 * @return {number|null}
 */
function getFirstNumber(line) {
  const numbers = line.match(/([0-9]+)/);
  if (numbers !== null) {
    return parseInt(numbers[0]);
  }

  return null;
}

/**
 * @param {HTMLElement} element
 * @param {string} css_class
 * @return {void}
 */
function toggleClassOfElement(element, css_class) {
  if (element.classList.contains(css_class)) {
    element.classList.remove(css_class);
  } else {
    element.classList.add(css_class);
  }
}

/**
 * @param {KeyboardEvent} event
 * @return {boolean}
 */
function isEnterKeyPressed(event) {
  if (event instanceof KeyboardEvent) {
    return (event.code === 'Enter');
  }

  return false;
}

/**
 * @param {string} line
 * @return {string}
 */
function removeBulletPointOrEnummeration(line) {
  return line.replace(/((^(\s*[-])|(^(\s*\d+\.)))\s*)/g, '');
}

/**
 * @param {string} line
 * @return {boolean}
 */
function isEmptyListEntry(line) {
  return ((line.match(/((^(\s*-)|(^(\s*\d+\.)))\s*)$/g) ?? []).length > 0);
}

/**
 * @param {string} line
 * @return {boolean}
 */
function isBulletPointed(line) {
  return ((line.match(/^(\s*[-])/g) ?? []).length > 0);
}

/**
 * @param {string} line
 * @return {boolean}
 */
function isEnumerated(line) {
  return ((line.match(/^(\s*\d+\.)/g) ?? []).length > 0);
}
