<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Importer class for forums
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id: $
 * @ingroup ModulesForum
 */
class ilForumImporter extends ilXmlImporter
{
    public function importXmlRepresentation(string $a_entity, string $a_id, string $a_xml, ilImportMapping $a_mapping) : void
    {
        if ($new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_id)) {
            $newObj = ilObjectFactory::getInstanceByObjId((int) $new_id, false);
        } else {
            $newObj = new ilObjForum();
            $newObj->setType('frm');
            $newObj->create();
        }

        $parser = new ilForumXMLParser($newObj, $a_xml);
        $parser->setImportDirectory($this->getImportDirectory());
        $parser->setImportInstallId($this->getInstallId());
        $parser->setSchemaVersion($this->getSchemaVersion());
        $parser->startParsing();

        $a_mapping->addMapping('Modules/Forum', 'frm', $a_id, (string) $newObj->getId());
    }
}
