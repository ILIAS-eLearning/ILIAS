<?php declare(strict_types = 1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

namespace ILIAS\Survey\Settings;

/**
 * Access settings
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class AccessSettings
{
    protected int $start_date;
    protected int $end_date;
    protected bool $access_by_codes;

    /**
     * @param int $start_date (unix ts)
     * @param int $end_date (unix ts)
     * @param bool $access_by_codes
     */
    public function __construct(
        int $start_date,
        int $end_date,
        bool $access_by_codes
    ) {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->access_by_codes = $access_by_codes;
    }

    /**
     * Get start date (unix ts)
     */
    public function getStartDate() : int
    {
        return $this->start_date;
    }

    /**
     * Get start date (unix ts)
     */
    public function getEndDate() : int
    {
        return $this->end_date;
    }

    /**
     * Get access by codes
     */
    public function getAccessByCodes() : bool
    {
        return $this->access_by_codes;
    }
}
