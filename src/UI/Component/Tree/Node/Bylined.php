<?php
declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Tree\Node;

/**
 * This describes a tree node with an byline providing additional information
 * about this node
 */
interface Bylined extends Simple
{
    /**
     * The byline string that will be displayed as additional
     * information to the current node
     * @return string
     */
    public function getBylined() : string;
}
