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
     * Creates a contribution to a user at a specific time.
     */
    public function __construct(string $content, ilObjUser $user, ilDateTime $datetime);

    /**
     * Get a copy of that contribution with another user.
     */
    public function withUser(ilObjUser $user) : Contribution;

    public function getUser() : ilObjUser;

    /**
     * Get a copy of that contribution with another datetime.
     */
    public function withDateTime(ilDateTime $dateTime) : Contribution;

    public function getDateTime() : ilDateTime;

    /**
     * Get a copy of that contribution with an url to consulted async, when the close button is pressed.
     */
    public function withClose(Close $close) : Contribution;

    public function getClose() : ?Close;

    /**
     * Set icon as lead
     */
    public function withLeadIcon(Icon $lead) : Contribution;

    public function getLeadIcon() : ?Icon;

    public function withIdentifier(string $identifier) : Contribution;

    public function getIdentifier() : ?string;
}
