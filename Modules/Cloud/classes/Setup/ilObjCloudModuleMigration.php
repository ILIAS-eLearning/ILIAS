<?php declare(strict_types=1);

use ILIAS\Setup\Migration;
use ILIAS\Setup\Environment;

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

class ilObjCloudModuleMigration implements Migration
{
    protected ilDBInterface $db;

    public function getLabel() : string
    {
        return 'ilObjCloudModule Data Removal. Attention, this deletes all Data of the Cloud Module from the Repository';
    }

    public function getDefaultAmountOfStepsPerRun() : int
    {
        return Migration::INFINITE;
    }

    public function getRemainingAmountOfSteps() : int
    {
        if ($this->db->fetchObject($this->getCloudReferencesQuery())) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * @return ilDatabaseUpdatedObjective[]|ilIniFilesLoadedObjective[]
     */
    public function getPreconditions(Environment $environment) : array
    {
        return [
            new ilIniFilesLoadedObjective(),
            new ilDatabaseUpdatedObjective()
        ];
    }

    public function prepare(Environment $environment) : void
    {
        //This is necessary for using ilObjects delete function to remove existing objects
        ilContext::init(ilContext::CONTEXT_CRON);
        ilInitialisation::initILIAS();
        $this->db = $environment->getResource(Environment::RESOURCE_DATABASE);
    }

    public function step(Environment $environment) : void
    {
        while ($result = $this->db->fetchObject($this->getCloudReferencesQuery())) {
            $cloud_object = new ilObjCloud((int) $result->ref_id);
            $cloud_object->delete();
        }
    }

    protected function getCloudReferencesQuery() : ilDBStatement
    {
        return $this->db->query("
                    SELECT ref_id 
                    FROM object_data, object_reference 
                    WHERE object_data.type = 'cld' AND object_data.obj_id = object_reference.obj_id");
    }
}
