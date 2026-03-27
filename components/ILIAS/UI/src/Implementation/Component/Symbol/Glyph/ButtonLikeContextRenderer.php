<?php

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

declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Symbol\Glyph;

use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Render\Template;

/**
 * Glyph components inside a Button or Bulky Link component MUST be
 * rendered with `aria-hidden="true"`, because the outer component
 * SHOULD provide the appropriate label and convey the information,
 * which renders the Glyph to be merely decorative.
 */
class ButtonLikeContextRenderer extends Renderer
{
    protected function renderAccessibilityInfo(Glyph $component, Template $tpl): void
    {
        // omit label, should be provided by outer component
        // omit role, glyph will be hidden
        // hide glyph in absence of label (semantic meaning)
        $tpl->touchBlock('with_aria_hidden');
    }
}
