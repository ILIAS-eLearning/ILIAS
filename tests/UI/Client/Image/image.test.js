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
 ******************************************************************** */

import { assert } from 'chai';
import { JSDOM } from 'jsdom';
import fs from 'fs';
import HighResImageLoader from '../../../../src/UI/templates/js/Image/src/image.HighResImageLoader';

/**
 * ID that should be used to initialize instances, it will be used when
 * setting up the mocked DOM.
 *
 * @type {string}
 */
const imageId = 'test_img_id';
const imageTemplate = String(fs.readFileSync('./src/UI/templates/default/Image/tpl.image.html'));
const imageSrc = './src/UI/examples/Image/mountains-144w.jpg';
const imageDefinitions = {
  300: './src/UI/examples/Image/mountains-301w.jpg',
  600: './src/UI/examples/Image/mountains-602w.jpg',
};

class MockImage {
  /**
     * @type {string}
     */
  src = '';

  /**
     * @type {object}
     */
  onload;

  constructor() {
    const me = this;
    const callOnLoad = setInterval(async () => {
      if (me.src !== '' && me.onload !== undefined) {
        me.onload();
        clearInterval(callOnLoad);
      }
    }, 5);
  }
}
/**
 * Initializes the global window and document variable that holds a mocked
 * DOM containing the textarea html.
 *
 * @param {string} type
 * @param {number} width
 * @param {bool} linked
 * @return {HTMLElement}
 */
function initImage(width, linked = false) {
  const imageSubTemplate = extractPart(imageTemplate, '<!-- BEGIN responsive -->', '<!-- END responsive -->');
  let imageSubTemplateWithReplacements = imageSubTemplate.replace('{SOURCE}', imageSrc);

  if (linked) {
    imageSubTemplateWithReplacements = initActionSections(imageSubTemplateWithReplacements)
      .replace('{IMG_ID}', ` width='${width}`);
  } else {
    imageSubTemplateWithReplacements = imageSubTemplateWithReplacements
      .replace('{IMG_ID}', ` width='${width}' id='${imageId}'`);
  }

  const dom = new JSDOM('');
  const div = dom.window.document.createElement('DIV');
  div.innerHTML = `${imageSubTemplateWithReplacements}`;
  return div.firstChild;
}

function initActionSections(imageSubTemplate) {
  const startSection = extractPart(imageTemplate, '<!-- BEGIN action_begin -->', '<!-- END action_begin -->')
    .replace('{ID}', ` id='${imageId}'`);
  const endSection = extractPart(imageTemplate, '<!-- BEGIN action_end -->', '<!-- END action_end -->');

  return startSection + imageSubTemplate + endSection;
}

function extractPart(text, startPattern, endPattern) {
  const matches = text.match(new RegExp(`${startPattern}\n(?<match>.*)\n${endPattern}`));
  return matches.groups.match;
}

describe('HighResImageLoader', () => {
  it('Returns right image for image without action.', () => {
    Object.keys(imageDefinitions).forEach(async (key) => {
      const img = initImage(key);
      img.ownerDocument.defaultView.Image = MockImage;
      await HighResImageLoader.loadHighResImage(img, imageDefinitions);

      assert.equal(img.src, imageDefinitions[key]);
    });
  });

  it('Returns right image for image with action.', () => {
    Object.keys(imageDefinitions).forEach(async (key) => {
      const img = initImage(key, true);
      img.ownerDocument.defaultView.Image = MockImage;
      await HighResImageLoader.loadHighResImage(img, imageDefinitions);

      assert.equal(img.src, imageDefinitions[key]);
    });
  });
});
