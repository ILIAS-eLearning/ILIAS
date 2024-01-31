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
 */

/**
 * Returns the <img> element for the given id. If the element associated with
 * the id is not an image itself, the element will be searched for one instead.
 *
 * @param {Document} document
 * @param {string} imageId
 * @return {HTMLImageElement|null}
 */
export default function getImageElement(document, imageId) {
  const imageElement = document.getElementById(imageId);
  if (imageElement === null) {
    return null;
  }

  if (imageElement instanceof document.defaultView.HTMLImageElement) {
    return imageElement;
  }

  return imageElement.querySelector('img');
}
