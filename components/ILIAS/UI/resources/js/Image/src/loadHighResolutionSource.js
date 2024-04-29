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
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 * @author Stephan Kergomard <office@kergomard.ch>
 */

/**
 * Returns the source for the largest possible min-width that is smaller or
 * equal to the given image width (best fitting source).
 *
 * @param {Map<number, string>} highResDefinitions
 * @param {number} imageWidth
 * @return {string|null}
 */
function determineBestSource(highResDefinitions, imageWidth) {
  let bestSource = null;
  let bestSize = null;

  highResDefinitions.forEach(
    (source, minWidth) => {
      if (minWidth <= imageWidth && minWidth > bestSize) {
        bestSize = minWidth;
        bestSource = source;
      }
    },
  );

  return bestSource;
}

/**
 * Loads the best fitting high resolution source for the given image element.
 *
 * @param {HTMLImageElement} imageElement
 * @param {Map<number, string>} highResDefinitions min-width in px => source mapping
 */
export default function loadHighResolutionSource(imageElement, highResDefinitions) {
  const optimalSource = determineBestSource(highResDefinitions, imageElement.width);
  if (optimalSource !== null) {
    const highResolutionImage = imageElement.cloneNode();
    highResolutionImage.addEventListener('load', () => {
      imageElement.replaceWith(highResolutionImage);
    });

    highResolutionImage.src = optimalSource;
  }
}
