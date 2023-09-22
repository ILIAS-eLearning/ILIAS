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

export default class HighResImageLoader {
  /**
     * Load a higher resolution image depending on the actual size of
     * the image on the screenb and the available additional high
     * resolution sources.
     *
     * @param {HTMLImgElement|HTMLAnchorElement} image
     * @param {object<number, string>} definitions Definitions of available high resolution sources.
     */
  static async loadHighResImage(image, definitions) {
    let img = image;
    if (image.nodeName === 'A') {
      img = image.firstChild;
    }

    const sortedSources = new Map(Object.entries(definitions).sort());
    const neededSource = HighResImageLoader.#determineBestSource(sortedSources, img.width);

    if (neededSource !== '') {
      img.src = await HighResImageLoader.#loadBestSource(neededSource, image.ownerDocument.defaultView.Image);
    }
  }

  /**
     * @param {Map<int, string>} sources
     * @param {number} expectedSize
     * @return {string}
     */
  static #determineBestSource(sources, expectedSize) {
    let neededSource = '';
    sources.forEach(
      (source, size) => {
        if (size <= expectedSize) {
          neededSource = source;
        }
      },
    );
    return neededSource;
  }

  /**
     * @param {string} source
     * @param {object} Image
     * @return {Promise<string>}
     */
  static #loadBestSource(source, Image) {
    return new Promise((resolve) => {
      const img = new Image();
      img.src = source;
      img.onload = () => resolve(source);
    });
  }
}
