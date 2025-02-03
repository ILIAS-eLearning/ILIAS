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

namespace ILIAS\UI\Implementation\Render;

use InvalidArgumentException;

/**
 * Plumbing for ILIAS, tries to guess
 * @author	Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilResourceRegistry implements ResourceRegistry
{
    public function __construct(
        protected \ILIAS\UICore\GlobalTemplate $il_template,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function register(string $name): void
    {
        $path_parts = pathinfo($name);
        switch ($path_parts["extension"]) {
            case "js":
                $this->il_template->addJavaScript($name, true, 1);
                break;
            case "css":
                $this->il_template->addCss($name);
                break;
            case "less":
                // Can be ignored, should be compiled into css
                break;
            default:
                throw new InvalidArgumentException("Can't handle resource '$name'");
        }
    }
}
