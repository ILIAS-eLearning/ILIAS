<?php
declare(strict_types = 1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Survey\Execution;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class DataFactory
{
    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * @param int $survey_id
     * @param int $user_id
     * @return Run
     */
    public function run(int $survey_id, int $user_id) : Run
    {
        return new Run($survey_id, $user_id);
    }
}
