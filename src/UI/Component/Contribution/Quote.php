<?php declare(strict_types=1);
/* Copyright (c) 2021 Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Contribution;

use ILIAS\UI\Component\Button\Close;
use ILIAS\UI\Component\Symbol\Icon\Icon;

/**
 * Interface Quote Contribution
 */
interface Quote extends Contribution
{
    /**
     * Create a new Contribution with .
     */
    public function withContent(string $content) : Quote;

    /**
     * Get the contributed content.
     */
    public function getContent() : string;

    /**
     * Get a copy of that contribution with a close button.
     */
    public function withClose(Close $close) : Quote;

    public function getClose() : ?Close;

    /**
     * Get a copy of that contribution with a lead icon.
     */
    public function withLeadIcon(Icon $lead) : Quote;

    public function getLeadIcon() : ?Icon;
}
