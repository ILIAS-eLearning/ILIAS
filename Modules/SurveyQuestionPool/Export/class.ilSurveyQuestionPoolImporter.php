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
 * Importer class for files
 * @author Helmut Schottmüller <ilias@aurealis.de>
 */
class ilSurveyQuestionPoolImporter extends ilXmlImporter
{
    public function importXmlRepresentation(
        string $a_entity,
        string $a_id,
        string $a_xml,
        ilImportMapping $a_mapping
    ) : void {
        // Container import => test object already created
        if ($new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_id)) {
            $newObj = ilObjectFactory::getInstanceByObjId($new_id, false);
        } else {	// case ii, non container
            // Shouldn't happen
            $GLOBALS['ilLog']->write(__METHOD__ . ': Called in non container mode');
            return;
        }

        $xml_file = $this->getXmlFileName();

        if (!file_exists($xml_file)) {
            $GLOBALS['ilLog']->write(__METHOD__ . ': Cannot find xml definition: ' . $xml_file);
            return;
        }

        // import qti data
        $newObj->importObject($xml_file);
        $a_mapping->addMapping(
            "Modules/SurveyQuestionPool",
            "spl",
            $a_id,
            $newObj->getId()
        );
    }
    
    protected function getXmlFileName() : string
    {
        $basename = basename($this->getImportDirectory());
        return $this->getImportDirectory() . '/' . $basename . '.xml';
    }
}
