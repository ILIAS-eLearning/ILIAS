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
  300: 'file:///srv/http/ilias-trunk/src/UI/examples/Image/mountains-301w.jpg',
  600: 'file:///srv/http/ilias-trunk/src/UI/examples/Image/mountains-602w.jpg',
};

function initMock() {
  HighResImageLoader.loadBestSource = function (source) {
    return new Promise((resolve) => {
      resolve(source);
    });
  };
}

/**
 * Initializes the global window and document variable that holds a mocked
 * DOM containing the textarea html.
 *
 * @param {string} type
 * @param {number} width
 * @return {HTMLElement}
 */
function initImage(type, width) {
  initMock();
  const startPattern = `<!-- BEGIN ${type} -->`;
  const endPattern = `<!-- END ${type} -->`;
  const imageSubTemplate = imageTemplate.match(new RegExp(`${startPattern}\n(?<image>.*)\n${endPattern}`))
  const imageSubTemplateWithReplacements = imageSubTemplate['groups'].image
    .replace('{IMG_ID}', ` width='${width}' id='${imageId}'`)
    .replace('{SOURCE}', imageSrc);
  const dom = new JSDOM();
  const div = dom.window.document.createElement('DIV');
  console.log(dom.window.document.location.toString());
  div.innerHTML = `${imageSubTemplateWithReplacements}`;
  const img = div.firstChild;
  return img;
}

describe('HighResImageLoader', () => {
  it('returns right image for standard image.', async () => {
    const img = initImage('responsive', 300);
    await HighResImageLoader.loadHighResImage(img, imageDefinitions);
    assert.equal(img.src, '');
  });
});
