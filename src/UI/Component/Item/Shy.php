<?php declare(strict_types=1);
/* Copyright (c) 2021 Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Item;

use ILIAS\UI\Component\Button\Close;
use ILIAS\UI\Component\Symbol\Icon\Icon;

/**
 * Interface Shy
 * @package ILIAS\UI\Component\Item
 */
interface Shy extends Item
{
    /**
     * Get a copy of that shy with a close button.
     */
    public function withClose(Close $close) : Shy;

    public function getClose() : ?Close;

    /**
     * Get a copy of that shy with a lead icon.
     */
    public function withLeadIcon(Icon $lead) : Shy;

    public function getLeadIcon() : ?Icon;
}
