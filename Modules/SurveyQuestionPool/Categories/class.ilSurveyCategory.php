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

/**
 * Survey category class
 * The ilSurveyCategory class encapsules a survey category
 * @author		Helmut Schottmüller <ilias@aurealis.de>
 * @todo make a proper dto, get rid of magic functions
 */
class ilSurveyCategory
{
    /** @var array{title: ?string, other: int, neutral: int, label: ?string, scale: ?int} */
    private array $arrData;

    public function __construct(
        ?string $title = null,
        int $other = 0,
        int $neutral = 0,
        ?string $label = null,
        ?int $scale = null
    ) {
        $this->arrData = array(
            "title" => $title,
            "other" => $other,
            "neutral" => $neutral,
            "label" => $label,
            "scale" => $scale
        );
    }

    /**
     * @param string $value
     * @return mixed
     */
    public function __get(string $value)
    {
        switch ($value) {
            case 'other':
            case 'neutral':
                return ($this->arrData[$value]) ? 1 : 0;
            default:
                return $this->arrData[$value] ?? null;
        }
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set(string $key, $value) : void
    {
        switch ($key) {
            default:
                $this->arrData[$key] = $value;
                break;
        }
    }
}
