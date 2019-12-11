<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlImporter.php");

/**
* folder xml importer
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @ingroup ModulesCategory
*/
class ilCategoryImporter extends ilXmlImporter
{
    private $category = null;
    

    public function init()
    {
    }
    
    /**
     * Import XML
     *
     * @param
     * @return
     */
    public function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
    {
        include_once './Modules/Category/classes/class.ilObjCategory.php';
        if ($new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_id)) {
            $refs = ilObject::_getAllReferences($new_id);
            $this->category = ilObjectFactory::getInstanceByRefId(end($refs), false);
        }
        // Mapping for containers without subitems
        elseif ($new_id = $a_mapping->getMapping('Services/Container', 'refs', 0)) {
            $this->category = ilObjectFactory::getInstanceByRefId($new_id, false);
        } elseif (!$this->category instanceof ilObjCategory) {
            $this->category = new ilObjCategory();
            $this->category->create(true);
        }

        include_once './Modules/Category/classes/class.ilCategoryXmlParser.php';

        try {
            $parser = new ilCategoryXmlParser($a_xml, 0);
            $parser->setCategory($this->category);
            $parser->setMode(ilCategoryXmlParser::MODE_UPDATE);
            $parser->startParsing();
            $a_mapping->addMapping('Modules/Category', 'cat', $a_id, $this->category->getId());
        } catch (ilSaxParserException $e) {
            $GLOBALS['ilLog']->write(__METHOD__ . ': Parsing failed with message, "' . $e->getMessage() . '".');
        } catch (Exception $e) {
            $GLOBALS['ilLog']->write(__METHOD__ . ': Parsing failed with message, "' . $e->getMessage() . '".');
        }
                            
        foreach ($a_mapping->getMappingsOfEntity('Services/Container', 'objs') as $old => $new) {
            $type = ilObject::_lookupType($new);
            
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
    
    /**
     * Final processing
     *
     * @param
     * @return
     */
    public function finalProcessing($a_mapping)
    {
        //echo "<pre>".print_r($a_mapping, true)."</pre>"; exit;
        // get all categories of the import
        include_once("./Services/Taxonomy/classes/class.ilObjTaxonomy.php");
        $maps = $a_mapping->getMappingsOfEntity("Modules/Category", "cat");
        foreach ($maps as $old => $new) {
            if ($old != "new_id" && (int) $old > 0) {
                // get all new taxonomys of this object
                $new_tax_ids = $a_mapping->getMapping("Services/Taxonomy", "tax_usage_of_obj", $old);
                $tax_ids = explode(":", $new_tax_ids);
                foreach ($tax_ids as $tid) {
                    ilObjTaxonomy::saveUsage($tid, $new);
                }
            }
        }
    }
}
