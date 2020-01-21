<?php
declare(strict_types=1);

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Tree;

use ILIAS\UI\Component\Tree as ITree;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;

/**
 * Tree Control
 */
class Expandable extends Tree implements ITree\Expandable
{
    use JavaScriptBindable;
}
