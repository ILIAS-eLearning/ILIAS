<?php declare(strict_types=1);

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

        return $writer->getXML();
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
