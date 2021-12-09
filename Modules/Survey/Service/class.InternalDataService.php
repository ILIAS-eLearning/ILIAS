<?php
declare(strict_types = 1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Survey;

use ILIAS\Survey\Settings\SettingsFactory;/**
 * Survey internal data service
 * @author killing@leifos.de
 */

class InternalDataService
{
    /**
     * @var Code\DataFactory
     */
    protected $code_factory;

    /**
     * @var Execution\DataFactory
     */
    protected $execution_factory;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->code_factory = new Code\DataFactory();
        $this->execution_factory = new Execution\DataFactory();
    }

    public function code(string $code) : Code\Code
    {
        return $this->code_factory->code($code);
    }

    public function run(int $survey_id, int $user_id) : Execution\Run
    {
        return $this->execution_factory->run($survey_id, $user_id);
    }

    public function settings() : Settings\SettingsFactory
    {
        return new Settings\SettingsFactory();
    }
}
