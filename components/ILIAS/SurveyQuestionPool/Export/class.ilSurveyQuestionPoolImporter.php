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

declare(strict_types=1);

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
    ): void {
        // Container import => test object already created
        if ($new_id = $a_mapping->getMapping('components/ILIAS/Container', 'objs', $a_id)) {
            $newObj = ilObjectFactory::getInstanceByObjId((int) $new_id, false);
        } else { // case ii, non container
            $newObj = new ilObjSurveyQuestionPool();
            $new_id = $newObj->create();
        }
        if (!$newObj instanceof ilObjSurveyQuestionPool) {
            throw new ilObjectNotFoundException("Could not create or load survey question pool " . $a_id);
        }

        # Try legacy import
        $xml_file = $this->getXmlFileName();
        if (file_exists($xml_file)) {
            $GLOBALS['ilLog']->info('Cannot find xml definition: ' . $xml_file);
            // import qti data
            $newObj->importObject($xml_file);
        }

        $import = new SurveyImportParser((int) $new_id, "", true, $a_mapping);
        $import->setXMLContent($a_xml);
        $import->startParsing();
        $a_mapping->addMapping(
            "components/ILIAS/SurveyQuestionPool",
            "spl",
            $a_id,
            (string) $newObj->getId()
        );
        $a_mapping->addMapping(
            'components/ILIAS/MetaData',
            'md',
            $a_id . ':0:spl',
            $newObj->getId() . ':0:spl'
        );
    }

    protected function getXmlFileName(): string
    {
        $basename = basename($this->getImportDirectory());
        return $this->getImportDirectory() . '/' . $basename . '.xml';
    }
}
