<?php

namespace ILIAS\MetaData\Paths\Navigator;

use ILIAS\MetaData\Elements\Structure\NullStructureElement;
use ILIAS\MetaData\Elements\Structure\StructureElementInterface;

class NullStructureNavigator extends NullBaseNavigator implements StructureNavigatorInterface
{
    public function nextStep(): ?StructureNavigatorInterface
    {
        return new NullStructureNavigator();
    }

    public function previousStep(): ?StructureNavigatorInterface
    {
        return new NullStructureNavigator();
    }

    public function elementAtFinalStep(): StructureElementInterface
    {
        return new NullStructureElement();
    }

    public function element(): StructureElementInterface
    {
        return new NullStructureElement();
    }
}
