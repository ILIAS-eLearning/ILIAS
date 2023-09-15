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

namespace ILIAS\MetaData\Paths\Navigator;

use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Elements\Structure\StructureElementInterface;

interface NavigatorFactoryInterface
{
    /**
     * Used to navigate on a metadata set along the given path.
     * If the path is relative, navigation starts at the given
     * element, otherwise it starts at the root of the set the
     * element is in.
     */
    public function navigator(
        PathInterface $path,
        ElementInterface $start_element
    ): NavigatorInterface;

    /**
     * Used to navigate on a metadata set structure along the given path.
     * If the path is relative, navigation starts at the given
     * element, otherwise it starts at the root of the set the
     * element is in.
     */
    public function structureNavigator(
        PathInterface $path,
        StructureElementInterface $start_element
    ): StructureNavigatorInterface;
}
