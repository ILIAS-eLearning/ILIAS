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

import { assert } from 'chai';
import loadHighResolutionSource
  from '../../../../../../components/ILIAS/UI/src/templates/js/Image/src/loadHighResolutionSource';

describe('loadHighResolutionSource', () => {
  it('should choose the best possible source.', () => {
    const initialSource = 'source_0';
    const sourceAbove10 = 'source_10';
    const sourceAbove20 = 'source_20';
    const sourceAbove30 = 'source_30';

    // note the definitions are NOT ordered by min-width
    const definitions = new Map([
      [10, sourceAbove10],
      [30, sourceAbove30],
      [20, sourceAbove20],
    ]);

    const expectedSources = new Map([
      [0, initialSource],
      [5, initialSource],
      [10, sourceAbove10],
      [15, sourceAbove10],
      [20, sourceAbove20],
      [25, sourceAbove20],
      [30, sourceAbove30],
      [35, sourceAbove30],
    ]);

    const imageElement = {
      width: 0,
      loader: null,
      src: initialSource,
      cloneNode() {
        return this;
      },
      replaceWith(otherImage) {
        this.src = otherImage.src;
      },
      addEventListener(e, fn) {
        this.loader = fn;
      },
    };

    for (let width = 0, maxWidth = 35; width <= maxWidth; width += 5) {
      imageElement.width = width;
      loadHighResolutionSource(imageElement, definitions);

      // we manually need to call the "load" event, since this will
      // actually replace the element, which updates the src property
      // in our mock image. If no image is found no loader is set.
      if (imageElement.loader !== null) {
        imageElement.loader();
        imageElement.loader = null;
      }

      assert.equal(imageElement.src, expectedSources.get(width));
    }
  });
});
