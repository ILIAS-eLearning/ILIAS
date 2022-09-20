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
 *
 * @author  Alex Killing <alex.killing@gmx.de>
 * @version $Id: $
 * @ingroup ModulesMediaPool
 */
class ilFileImporter extends ilXmlImporter
{
    protected ?ilObjFile $current_obj = null;

    /**
     * Import XML
     * @param
     */
    public function importXmlRepresentation(string $a_entity, string $a_id, string $a_xml, ilImportMapping $a_mapping): void
    {
        // case i container
        if ($new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_id)) {
            $newObj = ilObjectFactory::getInstanceByObjId($new_id, false);
            $newObj->setVersion(0); // If $version is 0 from database, it will be set to 1 in ilObFile::doRead(). In ilFileXMLParser::handlerBeginTag $version will being increased. So its incorrectly 2. Set $version to 0 like case ii, non container
        } else {    // case ii, non container
            $newObj = new ilObjFile();
            $newObj->setNoMetaDataCreation(true); // #16545
            $newObj->create(true);
        }

        $parser = new ilFileXMLParser($newObj, $a_xml);
        $parser->setImportDirectory($this->getImportDirectory());
        $parser->startParsing();

        $newObj->createProperties();

        $parser->setFileContents();
        $this->current_obj = $newObj;

        $newObj->update();        // this is necessary for case ii (e.g. wiki import)

        $a_mapping->addMapping("Modules/File", "file", $a_id, $newObj->getId());
        $a_mapping->addMapping(
            "Services/MetaData",
            "md",
            $a_id . ":0:file",
            $newObj->getId() . ":0:file"
        );
    }
}
