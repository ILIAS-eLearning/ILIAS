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
use ILIAS\MetaData\Paths\Steps\NavigatorBridge;
use ILIAS\MetaData\Paths\PathInterface;

class Navigator extends BaseNavigator implements NavigatorInterface
{
    public function __construct(
        PathInterface $path,
        ElementInterface $start_element,
        NavigatorBridge $bridge
    ) {
        parent::__construct($path, $start_element, $bridge);
    }

    public function nextStep(): ?NavigatorInterface
    {
        $return = parent::nextStep();
        if (($return instanceof NavigatorInterface) || is_null($return)) {
            return $return;
        }
        throw new \ilMDPathException('Invalid Navigator');
    }

    public function previousStep(): ?NavigatorInterface
    {
        $return = parent::previousStep();
        if (($return instanceof NavigatorInterface) || is_null($return)) {
            return $return;
        }
        throw new \ilMDPathException('Invalid Navigator');
    }

    /**
     * @return ElementInterface[]
     * @throws \ilMDPathException
     */
    public function elementsAtFinalStep(): \Generator
    {
        foreach (parent::elementsAtFinalStep() as $element) {
            if (!($element instanceof ElementInterface)) {
                throw new \ilMDElementsException(
                    'Invalid Navigator.'
                );
            }
            yield $element;
        }
    }

    /**
     * @throws \ilMDPathException
     */
    public function lastElementAtFinalStep(): ?ElementInterface
    {
        $element = parent::lastElementAtFinalStep();
        if (($element instanceof ElementInterface) || is_null($element)) {
            return $element;
        }
        throw new \ilMDPathException(
            'Invalid Navigator.'
        );
    }

    /**
     * @return ElementInterface[]
     * @throws \ilMDPathException
     */
    public function elements(): \Generator
    {
        foreach (parent::elements() as $element) {
            if (!($element instanceof ElementInterface)) {
                throw new \ilMDPathException(
                    'Invalid Navigator.'
                );
            }
            yield $element;
        }
    }


    /**
     * @throws \ilMDPathException
     */
    public function lastElement(): ?ElementInterface
    {
        $element = parent::lastElement();
        if (($element instanceof ElementInterface) || is_null($element)) {
            return $element;
        }
        throw new \ilMDPathException(
            'Invalid Navigator.'
        );
    }
}
