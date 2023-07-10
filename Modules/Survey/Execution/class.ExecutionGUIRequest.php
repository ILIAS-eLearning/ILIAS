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

namespace ILIAS\Survey\Execution;

use ILIAS\Repository\BaseGUIRequest;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ExecutionGUIRequest
{
    use BaseGUIRequest;

    protected array $params;

    public function __construct(
        \ILIAS\HTTP\Services $http,
        \ILIAS\Refinery\Factory $refinery
    ) {
        $this->initRequest(
            $http,
            $refinery
        );
    }

    public function getQuestionId(): int
    {
        return $this->int("qid");
    }

    public function getRefId(): int
    {
        return $this->int("ref_id");
    }

    public function getActiveCommand(): string
    {
        return $this->str("activecommand");
    }

    public function getAccessCode(): string
    {
        return $this->str("accesscode");
    }

    public function getDirection(): int
    {
        return $this->int("direction");
    }

    public function getMail(): string
    {
        return $this->str("mail");
    }

    public function getAppraiseeId(): int
    {
        return $this->int("appr_id");
    }

    public function getTargetPosition(): string
    {
        return $this->str("pgov");
    }

    public function getPreview(): int
    {
        return $this->int("prvw");
    }
}
