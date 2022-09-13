<?php

declare(strict_types=1);

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

namespace ILIAS\UI\Implementation\Component\Tree\Node;

use ILIAS\Data\URI;
use ILIAS\UI\Component\Tree\Node as INode;
use ILIAS\UI\Component\Symbol\Icon\Icon as IIcon;
use ILIAS\UI\Component\Tree\Node\Bylined as IByline;
use ILIAS\UI\Component\Tree\Node\KeyValue as IKeyValue;

class Factory implements INode\Factory
{
    /**
     * @inheritdoc
     */
    public function simple(string $label, IIcon $icon = null, URI $link = null): INode\Simple
    {
        return new Simple($label, $icon, $link);
    }

    public function bylined(string $label, string $byline, IIcon $icon = null): IByline
    {
        return new Bylined($label, $byline, $icon);
    }

    public function keyValue(string $label, string $value, IIcon $icon = null) : IKeyValue
    {
        return new KeyValue($label, $value, $icon);
    }
}
