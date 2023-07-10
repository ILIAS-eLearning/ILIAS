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
 * @author Alexander Killing <killing@leifos.de>
 */
class ilSurveyEvaluationResultsAnswer
{
    public int $active_id;
    public float $value;
    public string $text;
    public int $tstamp;

    public function __construct(
        int $a_active_id,
        float $a_value,
        string $a_text,
        int $a_tstamp
    ) {
        $this->active_id = $a_active_id;
        $this->value = $a_value;
        $this->text = trim($a_text);
        $this->tstamp = trim($a_tstamp);
    }
}
