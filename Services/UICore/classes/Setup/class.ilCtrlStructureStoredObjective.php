<?php declare(strict_types=1);

use ILIAS\Setup;

/**
 * Class ilCtrlStructureStoredObjective
 */
class ilCtrlStructureStoredObjective implements Setup\Objective
{
    /**
     * @var ilCtrlStructureReader
     */
    protected ilCtrlStructureReader $ctrl_reader;

    /**
     * ilCtrlStructureStoredObjective constructor.
     *
     * @param ilCtrlStructureReader $ctrl_reader
     */
    public function __construct(\ilCtrlStructureReader $ctrl_reader)
    {
        $this->ctrl_reader = $ctrl_reader;
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
        return [];
    }

    /**
     * @inheritdoc
     */
    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        if (!defined("ILIAS_ABSOLUTE_PATH")) {
            define("ILIAS_ABSOLUTE_PATH", dirname(__FILE__, 5));
        }

        $this->ctrl_reader->readStructure();

        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment) : bool
    {
        return !$this->ctrl_reader->isExecuted();
    }
}
