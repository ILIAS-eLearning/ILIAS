<?php
declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Tree\Node;

use ILIAS\UI\Component\Tree\Node as INode;
use ILIAS\UI\Component\Symbol\Icon\Icon as IIcon;

class Factory implements INode\Factory
{
    /**
     * @inheritdoc
     */
    public function simple(string $label, IIcon $icon=null) : INode\Simple
    {
        return new Simple($label, $icon);
    }
}
