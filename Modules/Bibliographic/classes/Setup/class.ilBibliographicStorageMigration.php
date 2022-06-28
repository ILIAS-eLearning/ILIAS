<?php

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

use ILIAS\ResourceStorage\Identification\ResourceIdentification;
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
        if ($identification === null) {
            $identification = 'failed';
        } else {
            $identification = $identification->serialize();
        }

        $this->helper->getDatabase()->manipulateF(
            'UPDATE `il_bibl_data` SET `rid` = %s WHERE `id` = %s;',
            ['text', 'integer'],
            [
                $identification,
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
