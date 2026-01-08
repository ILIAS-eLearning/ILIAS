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
 */

declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Implementation\Render\Template;

/**
 * This context renderer exists because the Table\Ordering component is some kind of
 * hybrid between an Input\Container\Container and a Table\Table. It was necessary
 * in order to remove the inline implementation of an Input\NameSource inside the
 * Table\Renderer. This is a huge code smell and should be properly addressed.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class OrderingTableContextRenderer extends Renderer
{
    protected function applyName(FormInput $component, Template $tpl): string
    {
        $name = $component->getDedicatedName();
        if (null === $name) {
            throw new \LogicException('Internal FormInput of Ordering Table MUST have a dedicated name.');
        }
        $tpl->setVariable("NAME", $name);
        return $name;
    }
}
