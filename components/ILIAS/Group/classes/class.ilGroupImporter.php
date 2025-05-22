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

declare(strict_types=1);
/**
* folder xml importer
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @ingroup components\ILIASGroup
*/
class ilGroupImporter extends ilXmlImporter
{
    private ?ilObjGroup $group = null;

    public function __construct()
    {
    }

    public function init(): void
    {
    }

    /**
     * @inheritDoc
     */
    public function importXmlRepresentation(
        string $a_entity,
        string $a_id,
        string $a_xml,
        ilImportMapping $a_mapping
    ): void {
        if ($new_id = $a_mapping->getMapping('components/ILIAS/Container', 'objs', $a_id)) {
            $refs = ilObject::_getAllReferences((int) $new_id);
            $ref_id = end($refs);
            $this->group = ilObjectFactory::getInstanceByRefId((int) $ref_id, false);
        } elseif ($new_id = $a_mapping->getMapping('components/ILIAS/Container', 'refs', "0")) {
            $this->group = ilObjectFactory::getInstanceByRefId((int) $new_id, false);
        } elseif (!$this->group instanceof ilObjGroup) {
            $this->group = new ilObjGroup();
            $this->group->create();
        }
        try {
            $parser = new ilGroupXMLParser($this->group, $a_xml, 0);
            $parser->setMode(ilGroupXMLParser::$UPDATE);

            // avoid duplicate MD sets
            $this->group->deleteMetaData();

            $parser->startParsing();
            $a_mapping->addMapping('components/ILIAS/Group', 'grp', $a_id, (string) $this->group->getId());
            $a_mapping->addMapping(
                'components/ILIAS/MetaData',
                'md',
                $a_id . ':0:grp',
                $this->group->getId() . ':0:grp'
            );

        } catch (ilSaxParserException | ilWebLinkXmlParserException $e) {
            $GLOBALS['DIC']->logger()->grp()->warning('Parsing failed with message, "' . $e->getMessage() . '".');
        }
    }
}
