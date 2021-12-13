<?php
declare(strict_types = 1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Survey\Execution;

/**
 * Stores access codes of anonymous session
 *
 * @author killing@leifos.de
 */
class AnonymousSessionRepo
{
    /**
     * Constructor
     */
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
