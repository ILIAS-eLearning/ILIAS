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

const RELEVANT_STYLES = [
  'padding-top', 'padding-bottom', 'padding-left', 'padding-right', 'margin-left', 'margin-right',
  'margin-top', 'margin-bottom', 'width', 'font-size', 'font-family', 'font-style', 'font-weight',
  'line-height', 'font-variant', 'text-transform', 'letter-spacing', 'border', 'box-sizing',
  'display',
];

const syncShadow = (textarea, shadow) => {
  const style = window.getComputedStyle(textarea);
  RELEVANT_STYLES.forEach(name => {
    shadow.style[name] = style[name];
  });
};

const willUpdate = textarea => {
  /** Prevent style update if style is already set. */
  let currentHeight = '';
  return newHeight => {
    if (newHeight !== currentHeight) {
      textarea.style.height = newHeight;
      currentHeight = newHeight;
    }
  };
};

/** Return the height which would be added on newline. */
const calculateLineHeight = shadow => {
  const value = shadow.value;
  shadow.value = '';
  const height = shadow.scrollHeight;
  shadow.value = '\n';
  const lineHeight = shadow.scrollHeight - height;
  shadow.value = value;
  return lineHeight;
};

const calculateTextareaHeight = (shadow, maxLines) => {
  const value = shadow.value;
  shadow.value = '\n'.repeat(maxLines - 1);
  const lineHeight = shadow.scrollHeight;
  shadow.value = value;
  return lineHeight;
};

const createShadow = textarea => {
  const shadow = document.createElement('textarea');
  shadow.style.height = window.getComputedStyle(textarea).height;
  shadow.setAttribute('area-hidden', 'true');
  shadow.readOnly = true;
  shadow.disabled = true;

  return shadow;
};

const freeze = thunk => {
  let thaw = () => {
    const value = thunk();
    thaw = () => value;
    return value;
  };

  return () => thaw();
};

export function expandableTextarea(shadowBoxSelector, textareaSelector, maxLines) {
  const select = selector => {
    const node = document.querySelector(selector);
    console.assert(node !== null, 'Could not find selector ' + JSON.stringify(selector));
    return node;
  };
  return expandableTextareaFromNodes(
    select(shadowBoxSelector),
    select(textareaSelector),
    maxLines
  );
}

export function expandableTextareaFromNodes(shadowBox, textarea, maxLines) {
  const shadow = createShadow(textarea);
  const updateHeight = willUpdate(textarea);
  const lineHeight = freeze(() => calculateLineHeight(shadow));

  /**
   * Max height of the textarea.
   * !! This is not equal to maxLines * lineHeight() because it includes the base height.
   */
  const maxTextareaHeight = freeze(() => calculateTextareaHeight(shadow, maxLines));

  const lines = (initial, currentHeight) => parseInt(
    ((currentHeight - initial) / lineHeight()) + 1
  );

  const resize = () => {
    shadow.value = '';
    const init = shadow.scrollHeight;
    const height = textarea.clientHeight;
    shadow.value = textarea.value;
    const scroll = shadow.scrollHeight;
    const currentLines = lines(init, scroll);
    if (scroll > init) {
      if (currentLines <= maxLines) {
        updateHeight(scroll + 'px');
      } else {
        updateHeight(maxTextareaHeight() + 'px');
      }
    } else if (scroll < height) {
      updateHeight('');
    }
  };

  return () => {
    shadowBox.appendChild(shadow);
    syncShadow(textarea, shadow);
    resize();
    shadow.remove();
  };
}
