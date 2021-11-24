<?php declare(strict_types=1);

/* Copyright (c) 2021 Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Item;

use ilDateTime;
use ILIAS\UI\Component\Button\Close;
use ILIAS\UI\Component\Symbol\Icon\Icon;
use ilObjUser;

/**
 * Interface Contribution
 * @package ILIAS\UI\Component\Item
 */
interface Contribution extends Item
{
    /**
     * Creates a contribution.
     */
    public function __construct(string $content, ?ilObjUser $user = null, ?ilDateTime $datetime = null);

    /**
     * Get a copy of that contribution with another user.
     */
    public function withUser(ilObjUser $user) : Contribution;

    public function getUser() : ?ilObjUser;

    /**
     * Get a copy of that contribution with another datetime.
     */
    public function withDateTime(ilDateTime $dateTime) : Contribution;

    public function getDateTime() : ?ilDateTime;

    /**
     * Get a copy of that contribution with a close button.
     */
    public function withClose(Close $close) : Contribution;

    public function getClose() : ?Close;

    /**
     * Get a copy of that contribution with a lead icon.
     */
    public function withLeadIcon(Icon $lead) : Contribution;

    public function getLeadIcon() : ?Icon;

    /**
     * Get a copy of that contribution with a unique identifier for further specification.
     */
    public function withIdentifier(string $identifier) : Contribution;

    public function getIdentifier() : ?string;
}
