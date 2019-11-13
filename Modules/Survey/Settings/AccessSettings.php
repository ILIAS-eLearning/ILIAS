<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Survey\Settings;

/**
 * Access settings
 *
 * @author killing@leifos.de
 */
class AccessSettings
{
    /**
     * @var int
     */
    protected $start_date;

    /**
     * @var int
     */
    protected $end_date;

    /**
     * @var bool
     */
    protected $access_by_codes;

    /**
     * Constructor
     * @param int $start_date (unix ts)
     * @param int $end_date (unix ts)
     * @param bool $access_by_codes
     */
    public function __construct(
        int $start_date,
        int $end_date,
        bool $access_by_codes
    )
    {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->access_by_codes = $access_by_codes;
    }

    /**
     * Get start date (unix ts)
     *
     * @return int
     */
    public function getStartDate(): int
    {
        return $this->start_date;
    }

    /**
     * Get start date (unix ts)
     *
     * @return int
     */
    public function getEndDate(): int
    {
        return $this->end_date;
    }

    /**
     * Get access by codes
     *
     * @return bool
     */
    public function getAccessByCodes(): bool
    {
        return $this->access_by_codes;
    }

}