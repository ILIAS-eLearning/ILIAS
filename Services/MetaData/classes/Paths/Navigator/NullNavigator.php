<?php

namespace ILIAS\MetaData\Paths\Navigator;

use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Elements\NullElement;

class NullNavigator extends NullBaseNavigator implements NavigatorInterface
{
    public function nextStep(): ?NavigatorInterface
    {
        return new NullNavigator();
    }

    public function previousStep(): ?NavigatorInterface
    {
        return new NullNavigator();
    }

    public function elementsAtFinalStep(): \Generator
    {
        yield from [];
    }

    public function lastElementAtFinalStep(): ?ElementInterface
    {
        return new NullElement();
    }

    public function elements(): \Generator
    {
        yield from [];
    }

    public function lastElement(): ?ElementInterface
    {
        return new NullElement();
    }
}
