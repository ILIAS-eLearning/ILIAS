<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once './Modules/Course/classes/class.ilCourseXMLWriter.php';
include_once './Services/Export/classes/class.ilXmlExporter.php';

/**
* Folder export
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @ingroup ServicesBooking
*/
class ilCourseExporter extends ilXmlExporter
{
    const ENTITY_OBJECTIVE = 'objectives';
    const ENTITY_MAIN = 'crs';
    
    private $writer = null;
    
    /**
     * @var ilLogger
     */
    protected $logger = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->logger = $GLOBALS['DIC']->logger()->crs();
    }
    
    /**
     * Init export
     * @return
     */
    public function init()
    {
    }
    
    /**
     * Get head dependencies
     *
     * @param		string		entity
     * @param		string		target release
     * @param		array		ids
     * @return		array		array of array with keys "component", entity", "ids"
     */
    public function getXmlExportHeadDependencies($a_entity, $a_target_release, $a_ids)
    {
        if ($a_entity != self::ENTITY_MAIN) {
            return array();
        }

        // always trigger container because of co-page(s)
        return array(
            array(
                'component'		=> 'Services/Container',
                'entity'		=> 'struct',
                'ids'			=> $a_ids
            )
        );
    }
    
    // begin-patch optes_lok_export
    public function getXmlExportTailDependencies($a_entity, $a_target_release, $a_ids)
    {
        $dependencies = array();
        if ($a_entity == self::ENTITY_MAIN) {
            $obj_id = 0;
            foreach ($a_ids as $id) {
                $obj_id = $id;
            }

            $dependencies[] = array(
                    'component'			=> 'Modules/Course',
                    'entity'			=> self::ENTITY_OBJECTIVE,
                    'ids'				=> $obj_id
            );
            
            include_once './Modules/Course/classes/Objectives/class.ilLOPage.php';
            include_once './Modules/Course/classes/class.ilCourseObjective.php';
            $page_ids = array();
            foreach (ilCourseObjective::_getObjectiveIds($obj_id) as $objective_id) {
                foreach (ilLOPage::getAllPages('lobj', $objective_id) as $page_id) {
                    $page_ids[] = ('lobj:' . $page_id['id']);
                }
            }
            
            if ($page_ids) {
                $dependencies[] = array(
                    'component' => 'Services/COPage',
                    'entity' => 'pg',
                    'ids' => $page_ids
                );
            }
        }
        return $dependencies;
    }
    // end-patch optes_lok_export
    
    
    /**
     * Get xml
     * @param object $a_entity
     * @param object $a_schema_version
     * @param object $a_id
     * @return
     */
    public function getXmlRepresentation($a_entity, $a_schema_version, $a_id)
    {
        $course_ref_id = end(ilObject::_getAllReferences($a_id));
        $course = ilObjectFactory::getInstanceByRefId($course_ref_id, false);
        
        // begin-patch optes_lok_export
        if ($a_entity == self::ENTITY_OBJECTIVE) {
            try {
                include_once './Modules/Course/classes/Objectives/class.ilLOXmlWriter.php';
                $writer = new ilLOXmlWriter($course_ref_id);
                $writer->write();
                return $writer->getXml();
            } catch (Exception $ex) {
                $this->logger->error('Export failed with message: ' . $ex->getMessage());
                // and throw
                throw $ex;
            }
        }
        // end-patch optes_lok_export
        
        if (!$course instanceof ilObjCourse) {
            $this->logger->warning($a_id . ' is not id of course instance.');
            return '';
        }
        
        $this->writer = new ilCourseXMLWriter($course);
        $this->writer->setMode(ilCourseXMLWriter::MODE_EXPORT);
        $this->writer->start();
        return $this->writer->xmlDumpMem(false);
    }
    
    /**
     * Returns schema versions that the component can export to.
     * ILIAS chooses the first one, that has min/max constraints which
     * fit to the target release. Please put the newest on top.
     *
     * @return
     */
    public function getValidSchemaVersions($a_entity)
    {
        return array(
            "4.1.0" => array(
                "namespace" => "http://www.ilias.de/Modules/Course/crs/4_1",
                "xsd_file" => "ilias_course_4_1.xsd",
                "uses_dataset" => false,
                "min" => "4.1.0",
                "max" => "4.4.999"),
            "5.0.0" => array(
                "namespace" => "http://www.ilias.de/Modules/Course/crs/5_0",
                "xsd_file" => "ilias_crs_5_0.xsd",
                "uses_dataset" => false,
                "min" => "5.0.0",
                "max" => "")
        );
    }
}
