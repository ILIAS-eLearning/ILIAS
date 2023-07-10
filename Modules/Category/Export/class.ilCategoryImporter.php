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
 * folder xml importer
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilCategoryImporter extends ilXmlImporter
{
    private ?ilObject $category = null;

    public function init(): void
    {
    }

    /**
     * @throws ilDatabaseException
     * @throws ilObjectNotFoundException
     */
    public function importXmlRepresentation(
        string $a_entity,
        string $a_id,
        string $a_xml,
        ilImportMapping $a_mapping
    ): void {
        if ($new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_id)) {
            $refs = ilObject::_getAllReferences((int) $new_id);
            $this->category = ilObjectFactory::getInstanceByRefId(end($refs), false);
        }
        // Mapping for containers without subitems
        elseif ($new_id = $a_mapping->getMapping('Services/Container', 'refs', '0')) {
            $this->category = ilObjectFactory::getInstanceByRefId((int) $new_id, false);
        } elseif (!$this->category instanceof ilObjCategory) {
            $this->category = new ilObjCategory();
            $this->category->create();
        }

        try {
            $parser = new ilCategoryXmlParser($a_xml, 0);
            $parser->setCategory($this->category);
            $parser->setMode(ilCategoryXmlParser::MODE_UPDATE);
            $parser->startParsing();
            $a_mapping->addMapping('Modules/Category', 'cat', $a_id, (string) $this->category->getId());
        } catch (ilSaxParserException | Exception $e) {
            $GLOBALS['ilLog']->write(__METHOD__ . ': Parsing failed with message, "' . $e->getMessage() . '".');
        }

        foreach ($a_mapping->getMappingsOfEntity('Services/Container', 'objs') as $old => $new) {
            $type = ilObject::_lookupType((int) $new);

            // see ilGlossaryImporter::importXmlRepresentation()
            // see ilTaxonomyDataSet::importRecord()

            $a_mapping->addMapping(
                "Services/Taxonomy",
                "tax_item",
                $type . ":obj:" . $old,
                $new
            );

            // this is since 4.3 does not export these ids but 4.4 tax node assignment needs it
            $a_mapping->addMapping(
                "Services/Taxonomy",
                "tax_item_obj_id",
                $type . ":obj:" . $old,
                $new
            );
        }
    }

    public function finalProcessing(
        ilImportMapping $a_mapping
    ): void {
        $maps = $a_mapping->getMappingsOfEntity("Modules/Category", "cat");
        foreach ($maps as $old => $new) {
            if ($old !== "new_id" && (int) $old > 0) {
                // get all new taxonomys of this object
                $new_tax_ids = $a_mapping->getMapping("Services/Taxonomy", "tax_usage_of_obj", (string) $old);
                $tax_ids = explode(":", (string) $new_tax_ids);
                foreach ($tax_ids as $tid) {
                    ilObjTaxonomy::saveUsage((int) $tid, (int) $new);
                }
            }
        }
    }
}
