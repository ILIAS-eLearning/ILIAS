<?php

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

require_once './Modules/Test/classes/inc.AssessmentConstants.php';

/**
 * A class defining marks for assessment test objects
 *
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 *
 * @version	$Id$
 * @ingroup ModulesTest
 */
class ASS_Mark
{
    /**
    * The short name of the mark, e.g. F or 3 or 1,3
    */
    public string $short_name;

    /**
    * The official name of the mark, e.g. failed, passed, befriedigend
    */
    public string $official_name;

    /**
    * The minimum percentage level reaching the mark. A float value between 0 and 100
    */
    public float $minimum_level = 0;

    /**
     * The passed status of the mark. 0 indicates that the mark is failed, 1 indicates that the mark is passed
    */
    public int $passed;

    public function __construct(
        string $short_name = "",
        string $official_name = "",
        float $minimum_level = 0,
        int $passed = 0
    ) {
        $this->setShortName($short_name);
        $this->setOfficialName($official_name);
        $this->setMinimumLevel($minimum_level);
        $this->setPassed($passed);
    }

    public function getShortName(): string
    {
        return $this->short_name;
    }

    public function getPassed(): int
    {
        return $this->passed;
    }

    public function getOfficialName(): string
    {
        return $this->official_name;
    }

    public function getMinimumLevel(): float
    {
        return $this->minimum_level;
    }

    public function setShortName(string $short_name = ""): void
    {
        $this->short_name = $short_name;
    }

    public function setPassed($passed = 0): void
    {
        $this->passed = $passed;
    }

    public function setOfficialName(string $official_name = ""): void
    {
        $this->official_name = $official_name;
    }

    public function setMinimumLevel($minimum_level): void
    {
        $minimum_level = (float) $minimum_level;

        if (($minimum_level >= 0) && ($minimum_level <= 100)) {
            $this->minimum_level = $minimum_level;
        } else {
            throw new Exception('Markstep: minimum level must be between 0 and 100');
        }
    }
}
