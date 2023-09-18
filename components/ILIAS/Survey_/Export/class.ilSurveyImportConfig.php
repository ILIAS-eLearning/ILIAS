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
 * Import configuration for learning modules
 * @author Jesús López <lopez@leifos.com>
 */
class ilSurveyImportConfig extends ilImportConfig
{
    protected int $svy_qpl_id = -1;

    public function setQuestionPoolID(int $a_svy_qpl_id): void
    {
        $this->svy_qpl_id = $a_svy_qpl_id;
    }

    public function getQuestionPoolID(): int
    {
        return $this->svy_qpl_id;
    }
}
