<?php declare(strict_types = 1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

namespace ILIAS\Survey\Execution;

/**
 * Stores access codes of anonymous session
 * @author Alexander Killing <killing@leifos.de>
 */
class AnonymousSessionRepo
{
    public function __construct()
    {
    }

    protected function getKey(int $survey_id) : string
    {
        return "anonymous_id_" . (string) $survey_id;
    }

    public function issetCode(int $survey_id) : bool
    {
        return (\ilSession::has($this->getKey($survey_id)) &&
            \ilSession::get($this->getKey($survey_id)) != "");
    }

    public function setCode(int $survey_id, string $code) : void
    {
        \ilSession::set($this->getKey($survey_id), $code);
    }

    public function getCode(int $survey_id) : string
    {
        if (\ilSession::has($this->getKey($survey_id))) {
            return \ilSession::get($this->getKey($survey_id));
        }
        return "";
    }
}
