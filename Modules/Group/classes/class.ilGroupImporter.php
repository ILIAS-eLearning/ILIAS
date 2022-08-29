<?php

declare(strict_types=1);
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* folder xml importer
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @ingroup ModulesGroup
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
        if ($new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_id)) {
            $refs = ilObject::_getAllReferences((int) $new_id);
            $ref_id = end($refs);
            $this->group = ilObjectFactory::getInstanceByRefId((int) $ref_id, false);
        } elseif ($new_id = $a_mapping->getMapping('Services/Container', 'refs', "0")) {
            $this->group = ilObjectFactory::getInstanceByRefId((int) $new_id, false);
        } elseif (!$this->group instanceof ilObjGroup) {
            $this->group = new ilObjGroup();
            $this->group->create();
        }
        try {
            $parser = new ilGroupXMLParser($this->group, $a_xml, 0);
            $parser->setMode(ilGroupXMLParser::$UPDATE);
            $parser->startParsing();
            $a_mapping->addMapping('Modules/Group', 'grp', $a_id, (string) $this->group->getId());
        } catch (ilSaxParserException | ilWebLinkXmlParserException $e) {
            $GLOBALS['DIC']->logger()->grp()->warning('Parsing failed with message, "' . $e->getMessage() . '".');
        }
    }
}
