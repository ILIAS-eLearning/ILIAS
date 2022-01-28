<?php declare(strict_types=1);

/* Copyright (c) 2019 Jesús López <lopez@leifos.com> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Panel\Secondary;

use ILIAS\UI\Component as C;

/**
 * Class Factory
 * @package ILIAS\UI\Implementation\Component\Panel
 */
class Factory implements C\Panel\Secondary\Factory
{
    /**
     * @inheritdoc
     */
    public function listing(string $title, array $item_groups) : C\Panel\Secondary\Listing
    {
        return new Listing($title, $item_groups);
    }

    /**
     * @inheritdoc
     */
    public function legacy(string $title, C\Legacy\Legacy $legacy) : C\Panel\Secondary\Legacy
    {
        return new Legacy($title, $legacy);
    }
}
