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

namespace ILIAS\UI\Implementation\Component\Button;

use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Render\Template;

/**
 * Button components which are **indirect** descendants of an Input Container
 * component MUST be rendered with `type="button"` attribute. This is because
 * an Input Container COULD render actual submit buttons, while other components
 * (primarily Input Field's) utilise Button components to perform actions, and
 * would implicitly inherit a `type="submit"` if rendered inside an HTML form.
*/
class IndirectInputContainerContextRenderer extends Renderer
{
    protected const string INDIRECT_INPUT_CONTAINER_TYPE = "button";

    protected function renderButtonType(Component\Button\Button $component, Template $tpl): void
    {
        // add type attribute to prevent form submissions
        $tpl->setVariable('TYPE', self::INDIRECT_INPUT_CONTAINER_TYPE);
    }
}
