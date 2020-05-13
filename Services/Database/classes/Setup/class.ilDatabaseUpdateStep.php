<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup\Objective\CallableObjective;
use ILIAS\Setup\Objective;
use ILIAS\Setup\Environment;

/**
 * This encapsulate one database update step which is a method on some
 * ilDatabaseUpdateSteps-object.
 *
 * This is a close collaborator of ilDatabaseUpdateSteps and should most probably
 * not be used standalone.
 */
class ilDatabaseUpdateStep extends CallableObjective
{
    /**
     * @var	string
     */
    protected $class_name;

    /**
     * @var string
     */
    protected $method_name;

    public function __construct(
        ilDatabaseUpdateSteps $parent,
        int $num,
        Objective ...$preconditions
    ) {
        $this->class_name = get_class($parent);
        $this->method_name = \ilDatabaseUpdateSteps::STEP_METHOD_PREFIX . $num;
        parent::__construct(
            function (Environment $env) use ($parent, $num) {
                $db = $env->getResource(Environment::RESOURCE_DATABASE);
                $log = $env->getResource(\ilDatabaseUpdateStepExecutionLog::class);
                if ($log) {
                    $log->started($this->class_name, $num);
                }
                call_user_func([$parent, $this->method_name], $db);
                if ($log) {
                    $log->finished($this->class_name, $num);
                }
            },
            "Database update step {$this->class_name}::{$this->method_name}",
            false,
            ...$preconditions
        );
    }


    /**
     * @inheritdocs
     */
    public function getHash() : string
    {
        return hash(
            "sha256",
            $this->class_name . "::" . $this->method_name
        );
    }
}
