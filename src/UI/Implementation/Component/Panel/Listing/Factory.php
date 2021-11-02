<?php declare(strict_types=1);

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Panel\Listing;

use ILIAS\UI\Component\Panel as P;

/**
 * Class Factory
 * @package ILIAS\UI\Implementation\Component\Panel
 */
class Factory implements P\Listing\Factory
{
    /**
     * @inheritdoc
     */
    public function standard(string $title, array $item_groups) : P\Listing\Standard
    {
        return new Standard($title, $item_groups);
    }
}
