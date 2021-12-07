<?php

use ILIAS\Setup;
use ILIAS\Setup\Environment;

/**
 * Class ilBibliographicStorageMigration
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
final class ilBibliographicStorageMigration implements Setup\Migration
{
    protected ilResourceStorageMigrationHelper $helper;

    /**
     * @inheritDoc
     */
    public function getLabel() : string
    {
        return "Migration of Bibliographies to the Resource Storage Service.";
    }

    /**
     * @inheritDoc
     */
    public function getDefaultAmountOfStepsPerRun() : int
    {
        return 10000;
    }

    /**
     * @inheritDoc
     */
    public function getPreconditions(Environment $environment) : array
    {
        return ilResourceStorageMigrationHelper::getPreconditions();
    }

    /**
     * @inheritDoc
     */
    public function prepare(Environment $environment) : void
    {
        $this->helper = new ilResourceStorageMigrationHelper(
            new ilObjBibliographicStakeholder(),
            $environment
        );
    }

    /**
     * @inheritDoc
     */
    public function step(Environment $environment) : void
    {
        $r = $this->helper->getDatabase()->query("SELECT *  FROM il_bibl_data 
                    JOIN object_data ON object_data.obj_id = il_bibl_data.id
                    WHERE rid IS NULL LIMIT 1");
        $d = $this->helper->getDatabase()->fetchObject($r);

        $file_path = $this->helper->getClientDataDir() . '/' . ilBibliographicSetupAgent::COMPONENT_DIR . '/' . $d->id . '/' . $d->filename;
        $identification = $this->helper->movePathToStorage($file_path, (int) $d->owner);

        $this->helper->getDatabase()->manipulateF(
            'UPDATE `il_bibl_data` SET `rid` = %s WHERE `id` = %s;',
            ['text', 'integer'],
            [
                $identification->serialize(),
                $d->id,
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function getRemainingAmountOfSteps() : int
    {
        $r = $this->helper->getDatabase()->query("SELECT COUNT(*) AS amount FROM il_bibl_data WHERE rid IS NULL OR rid = ''");
        $d = $this->helper->getDatabase()->fetchObject($r);

        return (int) $d->amount;
    }
}
