<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Export/classes/class.ilXmlExporter.php';

/**
 * Class ilChatroomExporter
 */
class ilChatroomExporter extends ilXmlExporter
{
    /**
     * @inheritdoc
     */
    public function init()
    {
    }

    /**
     * @inheritdoc
     */
    public function getXmlRepresentation($a_entity, $a_schema_version, $a_id)
    {
        $chat = ilObjectFactory::getInstanceByObjId($a_id, false);
        if (!($chat instanceof ilObjChatroom)) {
            $GLOBALS['DIC']->logger()->root()->warning($a_id . ' is not id of chatroom instance. Skipped generation of export XML.');
            return '';
        }

        require_once 'Modules/Chatroom/classes/class.ilChatroomXMLWriter.php';
        $writer = new ilChatroomXMLWriter($chat);
        $writer->start();

        return $writer->getXml();
    }

    /**
     * @inheritdoc
     */
    public function getValidSchemaVersions($a_entity)
    {
        return array(
            '5.3.0' => array(
                'namespace'    => 'http://www.ilias.de/Modules/Chatroom/chtr/5_3',
                'xsd_file'     => 'ilias_chtr_5_3.xsd',
                'uses_dataset' => false,
                'min'          => '5.3.0',
                'max'          => '5.3.999'
            )
        );
    }
}
