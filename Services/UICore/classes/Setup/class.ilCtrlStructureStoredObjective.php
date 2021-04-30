<?php declare(strict_types=1);

use ILIAS\Setup;

class ilCtrlStructureStoredObjective implements Setup\Objective
{
    const TABLE_CLASSFILES = "ctrl_classfile";
    const TABLE_CALLS = "ctrl_calls";

    /**
     * @var ilCtrlStructureReader
     */
    protected $ctrl_reader;

    /**
     * @var	bool
     */
    protected $populate_before;

    public function __construct(\ilCtrlStructureReader $ctrl_reader, bool $populate_before = true)
    {
        $this->ctrl_reader = $ctrl_reader;
        $this->populate_before = $populate_before;
    }

    /**
     * @inheritdoc
     */
    public function getHash() : string
    {
        return hash("sha256", self::class);
    }

    /**
     * @inheritdoc
     */
    public function getLabel() : string
    {
        return "ilCtrl-structure is read and stored.";
    }

    /**
     * @inheritdoc
     */
    public function isNotable() : bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getPreconditions(Setup\Environment $environment) : array
    {
        $config = $environment->getConfigFor('database');
        return [
            new \ilDatabaseUpdatedObjective($config, $this->populate_before)
        ];
    }

    /**
     * @inheritdoc
     */
    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        $db = $environment->getResource(Setup\Environment::RESOURCE_DATABASE);
        if (!$db) {
            throw new \UnachievableException("Need DB to store control-structure");
        }

        if (!defined("ILIAS_ABSOLUTE_PATH")) {
            define("ILIAS_ABSOLUTE_PATH", dirname(__FILE__, 5));
        }

        $reader = $this->ctrl_reader->withDB($db);
        $reader->executed = false;
        $reader->readStructure(true);
        return $environment;
    }
}
