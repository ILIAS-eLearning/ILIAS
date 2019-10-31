<?php
declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Tree\Node;

use ILIAS\Data\URI;
use ILIAS\UI\Component\Tree\Node as INode;
use ILIAS\UI\Component\Symbol\Icon\Icon as IIcon;
use ILIAS\UI\Component\Tree\Node\Bylined as IByline;
use \ILIAS\UI\Implementation\Component\Tree\Node\Bylined;

class Factory implements INode\Factory
{
    /**
     * @inheritdoc
     */
    public function simple(string $label, IIcon $icon = null, URI $link = null) : INode\Simple
    {
        return new Simple($label, $icon, $link);
    }

    public function bylined(string $label, string $byline, IIcon $icon = null) : IByline
    {
        return new Bylined($label, $byline, $icon);
    }
}
