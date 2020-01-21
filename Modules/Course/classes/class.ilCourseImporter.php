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
* @ingroup ModulesCourse
*/
class ilCourseImporter extends ilXmlImporter
{
    const ENTITY_MAIN = 'crs';
    const ENTITY_OBJECTIVE = 'objectives';
    
    private $course = null;
    
    private $final_processing_info = array();
    

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
        include_once './Modules/Course/classes/class.ilCourseXMLParser.php';
        include_once './Modules/Course/classes/class.ilObjCourse.php';
        
        
        if ($new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_id)) {
            $refs = ilObject::_getAllReferences($new_id);
            $this->course = ilObjectFactory::getInstanceByRefId(end($refs), false);
        #$this->course = ilObjectFactory::getInstanceByObjId($new_id,false);
        }
        // Mapping for containers without subitems
        elseif ($new_id = $a_mapping->getMapping('Services/Container', 'refs', 0)) {
            $this->course = ilObjectFactory::getInstanceByRefId($new_id, false);
        } elseif (!$this->course instanceof ilObjCourse) {
            $this->course = new ilObjCourse();
            $this->course->create(true);
        }
        
        if ($a_entity == self::ENTITY_OBJECTIVE) {
            $this->addFinalProcessingInfo($this->course, $a_entity, $a_xml);

            // import learning objectives => without materials and fixed questions.
            // These are handled in afterContainerImportProcessing
            include_once './Modules/Course/classes/Objectives/class.ilLOXmlParser.php';
            $parser = new ilLOXmlParser($this->course, $a_xml);
            $parser->setMapping($a_mapping);
            $parser->parse();
            return;
        }

        try {
            $parser = new ilCourseXMLParser($this->course);
            $parser->setXMLContent($a_xml);
            $parser->startParsing();

            // set course offline
            $this->course->setOfflineStatus(true);
            $this->course->update();

            $a_mapping->addMapping('Modules/Course', 'crs', $a_id, $this->course->getId());
        } catch (ilSaxParserException $e) {
            $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': Parsing failed with message, "' . $e->getMessage() . '".');
        } catch (Exception $e) {
            $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': Parsing failed with message, "' . $e->getMessage() . '".');
        }
    }
    
    

    /**
     *
     * @param \ilImportMapping $mapping
     */
    public function afterContainerImportProcessing(\ilImportMapping $mapping)
    {
        foreach ($this->final_processing_info as $info) {
            // import learning objectives
            include_once './Modules/Course/classes/Objectives/class.ilLOXmlParser.php';
            $parser = new ilLOXmlParser($info['course'], $info['xml']);
            $parser->setMapping($mapping);
            $parser->parseObjectDependencies();
            return;
        }
    }
    
    /**
     * Add information for final processing
     */
    protected function addFinalProcessingInfo($a_course, $a_entity, $a_xml)
    {
        $this->final_processing_info[] = array(
            'course' => $a_course,
            'entity' => $a_entity,
            'xml' => $a_xml
        );
    }
}
