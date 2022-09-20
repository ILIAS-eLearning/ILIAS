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

/**
 * Importer class for user data
 * @author Alexander Killing <killing@leifos.de>
 */
class ilUserImporter extends ilXmlImporter
{
    protected ilUserDataSet $ds;

    public function init(): void
    {
        $this->ds = new ilUserDataSet();
        $this->ds->setDSPrefix("ds");
        $this->ds->setImportDirectory($this->getImportDirectory());
    }

    public function importXmlRepresentation(
        string $a_entity,
        string $a_id,
        string $a_xml,
        ilImportMapping $a_mapping
    ): void {
        new ilDataSetImportParser(
            $a_entity,
            $this->getSchemaVersion(),
            $a_xml,
            $this->ds,
            $a_mapping
        );
    }

    public function finalProcessing(ilImportMapping $a_mapping): void
    {
        if (is_array($this->ds->multi)) {
            foreach ($this->ds->multi as $usr_id => $values) {
                $usr_obj = new ilObjUser($usr_id);

                if (isset($values["interests_general"])) {
                    $usr_obj->setGeneralInterests($values["interests_general"]);
                } else {
                    $usr_obj->setGeneralInterests();
                }
                if (isset($values["interests_help_offered"])) {
                    $usr_obj->setOfferingHelp($values["interests_help_offered"]);
                } else {
                    $usr_obj->setOfferingHelp();
                }
                if (isset($values["interests_help_looking"])) {
                    $usr_obj->setLookingForHelp($values["interests_help_looking"]);
                } else {
                    $usr_obj->setLookingForHelp();
                }

                $usr_obj->updateMultiTextFields();
            }
        }
    }
}
