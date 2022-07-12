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
 * Importer class for forums
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ModulesForum
 */
class ilForumImporter extends ilXmlImporter implements ilForumObjectConstants
{
    protected \ILIAS\Style\Content\DomainService $content_style_domain;

    public function init() : void
    {
        global $DIC;
        $this->content_style_domain = $DIC
            ->contentStyle()
            ->domain();
    }

    public function importXmlRepresentation(string $a_entity, string $a_id, string $a_xml, ilImportMapping $a_mapping) : void
    {
        if ($new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_id)) {
            $newObj = ilObjectFactory::getInstanceByObjId((int) $new_id, false);
        } else {
            $newObj = new ilObjForum();
            $newObj->setType('frm');
            $newObj->create();
        }

        /** @var ilObjForum $newObj */
        $parser = new ilForumXMLParser($newObj, $a_xml, $a_mapping);
        $parser->setImportDirectory($this->getImportDirectory());
        $parser->setImportInstallId($this->getInstallId());
        $parser->setSchemaVersion($this->getSchemaVersion());
        $parser->startParsing();

        $a_mapping->addMapping('Modules/Forum', 'frm', $a_id, (string) $newObj->getId());
    }

    public function finalProcessing(ilImportMapping $a_mapping) : void
    {
        parent::finalProcessing($a_mapping);

        $copaMap = $a_mapping->getMappingsOfEntity('Services/COPage', 'pg');
        foreach ($copaMap as $oldCopaId => $newCopaId) {
            $newCopaId = (int) substr($newCopaId, strlen(self::OBJ_TYPE) + 1);

            ilForumPage::_writeParentId(self::OBJ_TYPE, $newCopaId, $newCopaId);
        }

        $styleMapping = $a_mapping->getMappingsOfEntity('Modules/Forum', 'style');
        foreach ($styleMapping as $newForumId => $oldStyleId) {
            $newStyleId = (int) $a_mapping->getMapping('Services/Style', 'sty', $oldStyleId);
            if ($newForumId > 0 && $newStyleId > 0) {
                $frm = ilObjectFactory::getInstanceByObjId((int) $newForumId, false);
                if (!($frm instanceof ilObjForum)) {
                    continue;
                }
                $this->content_style_domain
                    ->styleForObjId($newForumId)
                    ->updateStyleId($newStyleId);
            }
        }
    }
}
