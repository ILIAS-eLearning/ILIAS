<?php

declare(strict_types=1);

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
 * Class ilChatroomImporter
 */
class ilChatroomImporter extends ilXmlImporter
{
    public function importXmlRepresentation(string $a_entity, string $a_id, string $a_xml, ilImportMapping $a_mapping): void
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
