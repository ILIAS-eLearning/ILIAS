<?php declare(strict_types=1);

use ILIAS\Setup;
use ILIAS\DI;

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
        return [
            new \ilDatabaseInitializedObjective()
        ];
    }

    /**
     * @inheritdoc
     */
    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        $db = $environment->getResource(Setup\Environment::RESOURCE_DATABASE);
        if (!$db) {
            throw new Setup\UnachievableException("Need DB to store control-structure");
        }

        if (!defined("ILIAS_ABSOLUTE_PATH")) {
            define("ILIAS_ABSOLUTE_PATH", dirname(__FILE__, 5));
        }

        // ATTENTION: This is a total abomination. It only exists to allow various
        // sub components of the various readers to run. This is a memento to the
        // fact, that dependency injection is something we want. Currently, every
        // component could just service locate the whole world via the global $DIC.
        $DIC = $GLOBALS["DIC"];
        $GLOBALS["DIC"] = new DI\Container();
        $GLOBALS["DIC"]["ilDB"] = $db;

        $reader = $this->ctrl_reader->withDB($db);
        $reader->executed = false;
        $reader->readStructure(true);

        $GLOBALS["DIC"] = $DIC;

        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment) : bool
    {
        $db = $environment->getResource(Setup\Environment::RESOURCE_DATABASE);
        if (!$db) {
            throw new Setup\UnachievableException("Need DB to read control-structure");
        }

        if (!defined("ILIAS_ABSOLUTE_PATH")) {
            define("ILIAS_ABSOLUTE_PATH", dirname(__FILE__, 5));
        }

        $reader = $this->ctrl_reader->withDB($db);

        return !$reader->executed;
    }
}
