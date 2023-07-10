<?php

declare(strict_types=1);

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

namespace ILIAS\Survey\Evaluation;

/**
 * Stores access codes of anonymous session
 * @author Alexander Killing <killing@leifos.de>
 */
class EvaluationSessionRepo
{
    protected const KEY_BASE = "svy_eval_";
    protected const KEY_ANON_EVAL = self::KEY_BASE . "anon_eval";

    public function __construct()
    {
    }

    public function setAnonEvaluationAccess(int $ref_id): void
    {
        \ilSession::set(self::KEY_ANON_EVAL, $ref_id);
    }

    public function getAnonEvaluationAccess(): int
    {
        return (int) \ilSession::get(self::KEY_ANON_EVAL);
    }

    public function clearAnonEvaluationAccess(): void
    {
        \ilSession::clear(self::KEY_ANON_EVAL);
    }
}
