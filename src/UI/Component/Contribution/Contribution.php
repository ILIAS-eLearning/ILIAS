<?php declare(strict_types=1);
/* Copyright (c) 2021 Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Contribution;

use DateTimeImmutable;
use ILIAS\Data\DateFormat\DateFormat;
use ILIAS\UI\Component\Component;

/**
 * Common interface to all contributions.
 */
interface Contribution extends Component
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
    public function withDateFormat(DateFormat $dateFormat) : Contribution;

    public function getDateFormat() : DateFormat;

    /**
     * Get a copy of that contribution with a unique identifier for further specification.
     */
    public function withIdentifier(string $identifier) : Contribution;

    public function getIdentifier() : ?string;
}
