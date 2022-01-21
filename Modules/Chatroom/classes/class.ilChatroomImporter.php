<?php declare(strict_types=1);
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilChatroomImporter
 */
class ilChatroomImporter extends ilXmlImporter
{
    public function importXmlRepresentation(string $a_entity, string $a_id, string $a_xml, ilImportMapping $a_mapping) : void
    {
        if ($new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_id)) {
            $newObj = ilObjectFactory::getInstanceByObjId((int) $new_id, false);
        } else {
            $newObj = new ilObjChatroom();
            $newObj->setTitle('');
            $newObj->setDescription('');
            $newObj->setType('chtr');
            $newObj->create();
        }

        $parser = new ilChatroomXMLParser($newObj, $a_xml);
        $parser->setImportInstallId($this->getInstallId());
        $parser->startParsing();

        $a_mapping->addMapping('Modules/Chatroom', 'chtr', $a_id, (string) $newObj->getId());
    }
}
