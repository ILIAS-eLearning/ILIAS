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

use ILIAS\MetaData\Elements\Structure\StructureElementInterface;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Paths\Steps\NavigatorBridge;

class StructureNavigator extends BaseNavigator implements StructureNavigatorInterface
{
    public function __construct(
        PathInterface $path,
        StructureElementInterface $start_element,
        NavigatorBridge $bridge
    ) {
        parent::__construct($path, $start_element, $bridge);
        $this->leadsToOne(true);
    }

    public function nextStep(): ?StructureNavigatorInterface
    {
        $return = parent::nextStep();
        if (($return instanceof StructureNavigatorInterface) || is_null($return)) {
            return $return;
        }
        throw new \ilMDPathException('Invalid Navigator');
    }

    public function previousStep(): ?StructureNavigatorInterface
    {
        $return = parent::previousStep();
        if (($return instanceof StructureNavigatorInterface) || is_null($return)) {
            return $return;
        }
        throw new \ilMDPathException('Invalid Navigator');
    }

    public function elementAtFinalStep(): StructureElementInterface
    {
        $element = parent::elementsAtFinalStep()->current();
        if (!($element instanceof StructureElementInterface)) {
            throw new \ilMDPathException(
                'Invalid Navigator.'
            );
        }
        return $element;
    }

    public function element(): StructureElementInterface
    {
        $element = parent::elements()->current();
        if (!($element instanceof StructureElementInterface)) {
            throw new \ilMDPathException(
                'Invalid Navigator.'
            );
        }
        return $element;
    }
}
