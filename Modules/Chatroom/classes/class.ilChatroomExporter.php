<?php declare(strict_types=1);
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilChatroomExporter
 */
class ilChatroomExporter extends ilXmlExporter
{
    public function init()
    {
    }

    public function getXmlRepresentation($a_entity, $a_schema_version, $a_id)
    {
        $chat = ilObjectFactory::getInstanceByObjId($a_id, false);
        if (!($chat instanceof ilObjChatroom)) {
            $GLOBALS['DIC']->logger()->root()->warning(
                $a_id . ' is not id of chatroom instance. Skipped generation of export XML.'
            );
            return '';
        }

        $writer = new ilChatroomXMLWriter($chat);
        $writer->start();

        return $writer->getXml();
    }

    public function getXmlExportTailDependencies($a_entity, $a_target_release, $a_ids)
    {
        $deps = [];

        if ('chtr' === $a_entity) {
            $deps[] = [
                'component' => 'Services/Object',
                'entity' => 'common',
                'ids' => $a_ids
            ];
        }

        return $deps;
    }

    public function getValidSchemaVersions($a_entity)
    {
        return [
            '5.3.0' => [
                'namespace' => 'http://www.ilias.de/Modules/Chatroom/chtr/5_3',
                'xsd_file' => 'ilias_chtr_5_3.xsd',
                'uses_dataset' => false,
                'min' => '5.3.0',
                'max' => ''
            ]
        ];
    }
}
