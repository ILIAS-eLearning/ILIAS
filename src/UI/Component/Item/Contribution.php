<?php declare(strict_types=1);
/* Copyright (c) 2021 Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Item;

use DateTimeImmutable;
use ILIAS\Data\DateFormat\DateFormat;
use ILIAS\UI\Component\Button\Close;
use ILIAS\UI\Component\Symbol\Icon\Icon;

/**
 * Interface Contribution
 * @package ILIAS\UI\Component\Item
 */
interface Contribution extends Item
{
    /**
     * Get a copy of that contribution with another contributor.
     */
    public function withContributor(string $contributor) : Contribution;

    public function getContributor() : ?string;

    /**
     * Get a copy of that contribution with another datetime of creation.
     */
    public function withCreateDatetime(DateTimeImmutable $createDatetime) : Contribution;

    public function getCreateDatetime() : ?DateTimeImmutable;

    /**
     * Get a copy of that contribution with another date format.
     */
    public function withDateFormat(DateFormat $datetime) : Contribution;

    public function getDateFormat() : DateFormat;

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
