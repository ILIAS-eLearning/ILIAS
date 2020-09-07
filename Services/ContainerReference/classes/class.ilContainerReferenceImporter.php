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
* @ingroup ModulesContainerReference
*/
abstract class ilContainerReferenceImporter extends ilXmlImporter
{
    protected $ref = null;
    

    public function init()
    {
    }
    
    /**
     * Init reference
     * @return ilContainerReference
     */
    protected function initReference($a_ref_id = 0)
    {
        $this->ref = ilObjectFactory::getInstanceByRefId($a_ref_id, true);
    }
    
    /**
     * Get reference type
     */
    abstract protected function getType();
    
    /**
     * Init xml parser
     */
    abstract protected function initParser($a_xml);
    
    /**
     * get reference
     * @return ilContainerReference
     */
    protected function getReference()
    {
        return $this->ref;
    }
    
    /**
     * Import XML
     *
     * @param
     * @return
     */
    public function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
    {
        global $DIC;

        $objDefinition = $DIC["objDefinition"];
        $log = $DIC->logger()->root();

        include_once './Modules/Category/classes/class.ilObjCategory.php';
        if ($new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_id)) {
            $refs = ilObject::_getAllReferences($new_id);
            $this->initReference(end($refs));
        }
        // Mapping for containers without subitems
        elseif ($new_id = $a_mapping->getMapping('Services/Container', 'refs', 0)) {
            $this->initReference($new_id);
        } elseif (!$this->getReference() instanceof ilContainerReference) {
            $this->initReference();
            $this->getReference()->create(true);
        }

        try {
            $parser = $this->initParser($a_xml);
            $parser->setReference($this->getReference());
            $parser->setMode(ilContainerReferenceXmlParser::MODE_UPDATE);
            $parser->startParsing();
            
            $a_mapping->addMapping(
                $objDefinition->getComponentForType($this->getType()),
                $this->getType(),
                $a_id,
                $this->getReference()->getId()
            );
        } catch (ilSaxParserException $e) {
            $log->error(__METHOD__ . ': Parsing failed with message, "' . $e->getMessage() . '".');
        } catch (Exception $e) {
            $log->error(__METHOD__ . ': Parsing failed with message, "' . $e->getMessage() . '".');
        }
    }
}
