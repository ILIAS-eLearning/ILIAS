<?php

namespace ILIAS\MetaData\Repository\Utilities;

use ILIAS\MetaData\Elements\ElementInterface;

class NullScaffoldProvider implements ScaffoldProviderInterface
{
    public function getScaffoldsForElement(ElementInterface $element): \Generator
    {
        yield from [];
    }
}
