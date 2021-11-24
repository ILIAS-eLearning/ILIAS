<?php declare(strict_types=1);
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilChatroomExporter
 */
class ilChatroomExporter extends ilXmlExporter
{
    public function init() : void
    {
    }

    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $a_id) : string
    {
        $chat = ilObjectFactory::getInstanceByObjId((int) $a_id, false);
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

    public function getXmlExportTailDependencies(string $a_entity, string $a_target_release, array $a_ids) : array
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

    public function getValidSchemaVersions(string $a_entity) : array
    {
        return [
            '5.3.0' => [
                'namespace' => 'https://www.ilias.de/Modules/Chatroom/chtr/5_3',
                'xsd_file' => 'ilias_chtr_5_3.xsd',
                'uses_dataset' => false,
                'min' => '5.3.0',
                'max' => ''
            ]
        ];
    }
}
