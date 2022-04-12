<?php declare(strict_types=0);
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
 * Folder export
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesBooking
 */
class ilCourseExporter extends ilXmlExporter
{
    public const ENTITY_OBJECTIVE = 'objectives';
    public const ENTITY_MAIN = 'crs';

    protected ilXmlWriter $writer;
    protected ilLogger $logger;

    public function __construct()
    {
        global $DIC;
        $this->logger = $DIC->logger()->crs();
    }

    public function init() : void
    {
    }

    /**
     * Get head dependencies
     */
    public function getXmlExportHeadDependencies(string $a_entity, string $a_target_release, array $a_ids) : array
    {
        if ($a_entity != self::ENTITY_MAIN) {
            return array();
        }

        // always trigger container because of co-page(s)
        return array(
            array(
                'component' => 'Services/Container',
                'entity' => 'struct',
                'ids' => $a_ids
            )
        );
    }

    public function getXmlExportTailDependencies(string $a_entity, string $a_target_release, array $a_ids) : array
    {
        $dependencies = array();
        if ($a_entity == self::ENTITY_MAIN) {
            $obj_id = 0;
            foreach ($a_ids as $id) {
                $obj_id = $id;
            }

            $dependencies[] = array(
                'component' => 'Modules/Course',
                'entity' => self::ENTITY_OBJECTIVE,
                'ids' => $obj_id
            );

            $page_ids = array();
            foreach (ilCourseObjective::_getObjectiveIds($obj_id) as $objective_id) {
                foreach (ilLOPage::getAllPages('lobj', $objective_id) as $page_id) {
                    $page_ids[] = ('lobj:' . $page_id['id']);
                }
            }

            if ($page_ids !== []) {
                $dependencies[] = array(
                    'component' => 'Services/COPage',
                    'entity' => 'pg',
                    'ids' => $page_ids
                );
            }
        }
        return $dependencies;
    }

    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $a_id) : string
    {
        $refs = ilObject::_getAllReferences((int) $a_id);
        $course_ref_id = end($refs);
        $course = ilObjectFactory::getInstanceByRefId($course_ref_id, false);

        // begin-patch optes_lok_export
        if ($a_entity == self::ENTITY_OBJECTIVE) {
            try {
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

    public function getValidSchemaVersions(string $a_entity) : array
    {
        return array(
            "4.1.0" => array(
                "namespace" => "http://www.ilias.de/Modules/Course/crs/4_1",
                "xsd_file" => "ilias_course_4_1.xsd",
                "uses_dataset" => false,
                "min" => "4.1.0",
                "max" => "4.4.999"
            ),
            "5.0.0" => array(
                "namespace" => "http://www.ilias.de/Modules/Course/crs/5_0",
                "xsd_file" => "ilias_crs_5_0.xsd",
                "uses_dataset" => false,
                "min" => "5.0.0",
                "max" => ""
            )
        );
    }
}
