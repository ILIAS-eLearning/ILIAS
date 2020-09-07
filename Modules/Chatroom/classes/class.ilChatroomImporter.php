<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Export/classes/class.ilXmlImporter.php';

/**
 * Class ilChatroomImporter
 */
class ilChatroomImporter extends ilXmlImporter
{
    /**
     * Import XML
     *
     * @param
     * @return
     */
    public function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
    {
        require_once 'Modules/Chatroom/classes/class.ilObjChatroom.php';

        if ($new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_id)) {
            $newObj = ilObjectFactory::getInstanceByObjId($new_id, false);
        } else {
            $newObj = new ilObjChatroom();
            $newObj->setType('chtr');
            $newObj->create();
        }

        require_once 'Modules/Chatroom/classes/class.ilChatroomXMLParser.php';
        $parser = new ilChatroomXMLParser($newObj, $a_xml);
        $parser->setImportInstallId($this->getInstallId());
        $parser->startParsing();

        $a_mapping->addMapping('Modules/Chatroom', 'chtr', $a_id, $newObj->getId());
    }
}
