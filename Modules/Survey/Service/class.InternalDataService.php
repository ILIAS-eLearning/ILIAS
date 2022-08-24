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

namespace ILIAS\Survey;

/**
 * Survey internal data service
 * @author Alexander Killing <killing@leifos.de>
 */
class InternalDataService
{
    protected Code\DataFactory $code_factory;
    protected Execution\DataFactory $execution_factory;

    public function __construct()
    {
        $this->code_factory = new Code\DataFactory();
        $this->execution_factory = new Execution\DataFactory();
    }

    public function code(string $code): Code\Code
    {
        return $this->code_factory->code($code);
    }

    public function run(int $survey_id, int $user_id): Execution\Run
    {
        return $this->execution_factory->run($survey_id, $user_id);
    }

    public function settings(): Settings\SettingsFactory
    {
        return new Settings\SettingsFactory();
    }
}
