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
import getImageElement from '../../../../../../components/ILIAS/UI/src/templates/js/Image/src/getImageElement';

class HTMLImageElementMock {
  constructor(id) {
    this.id = id;
  }
}

class HTMLAnchorElementMock {
  constructor(imageElement) {
    this.imageElement = imageElement;
  }

  querySelector() {
    return this.imageElement;
  }
}

describe('getImageElement', () => {
  it('should find an image inside a link.', () => {
    const expectedImageId = 'img1';
    const expectedImage = new HTMLImageElementMock(expectedImageId);
    const document = {
      defaultView: {
        HTMLImageElement: HTMLImageElementMock,
      },
      getElementById: () => new HTMLAnchorElementMock(expectedImage),
    };

    const imageElement = getImageElement(document, '');

    assert.equal(imageElement.id, expectedImageId);
  });

  it('should find an image directly associated to the id.', () => {
    const expectedImageId = 'img2';
    const expectedImage = new HTMLImageElementMock(expectedImageId);
    const document = {
      defaultView: {
        HTMLImageElement: HTMLImageElementMock,
      },
      getElementById: () => expectedImage,
    };

    const imageElement = getImageElement(document, '');

    assert.equal(imageElement.id, expectedImageId);
  });

  it('should return null if no image was found.', () => {
    const document = {
      defaultView: {
        HTMLImageElement: HTMLImageElementMock,
      },
      getElementById: () => new HTMLAnchorElementMock(null),
    };

    const imageElement1 = getImageElement(document, '');
    assert.isNull(imageElement1);

    document.getElementById = () => null;
    const imageElement2 = getImageElement(document, '');
    assert.isNull(imageElement2);
  });
});
