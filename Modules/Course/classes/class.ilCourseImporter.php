<?php declare(strict_types=0);
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ModulesCourse
 */
class ilCourseImporter extends ilXmlImporter
{
    public const ENTITY_MAIN = 'crs';
    public const ENTITY_OBJECTIVE = 'objectives';

    private ?ilObjCourse $course = null;
    private $final_processing_info = array();

    protected ilLogger $logger;

    public function __construct()
    {
        global $DIC;

        $this->logger = $DIC->logger()->crs();
    }

    public function init() : void
    {
    }

    /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
    public function importXmlRepresentation(
        string $a_entity,
        string $a_id,
        string $a_xml,
        ilImportMapping $a_mapping
    ) : void {
        if ($new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_id)) {
            $refs = ilObject::_getAllReferences((int) $new_id);
            $this->course = ilObjectFactory::getInstanceByRefId((int) end($refs), false);
        } // Mapping for containers without subitems
        elseif ($new_id = $a_mapping->getMapping('Services/Container', 'refs', '0')) {
            $this->course = ilObjectFactory::getInstanceByRefId((int) $new_id, false);
        } elseif (!$this->course instanceof ilObjCourse) {
            $this->course = new ilObjCourse();
            $this->course->create(true);
        }

        if ($a_entity == self::ENTITY_OBJECTIVE) {
            $this->addFinalProcessingInfo($this->course, $a_entity, $a_xml);

            // import learning objectives => without materials and fixed questions.
            // These are handled in afterContainerImportProcessing
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

            $a_mapping->addMapping('Modules/Course', 'crs', $a_id, (string) $this->course->getId());
        } catch (ilSaxParserException|Exception $e) {
            $this->logger->error('Parsing failed with message, "' . $e->getMessage() . '".');
        }
    }

    public function afterContainerImportProcessing(\ilImportMapping $mapping) : void
    {
        foreach ($this->final_processing_info as $info) {
            // import learning objectives
            $parser = new ilLOXmlParser($info['course'], $info['xml']);
            $parser->setMapping($mapping);
            $parser->parseObjectDependencies();
            return;
        }
    }

    protected function addFinalProcessingInfo($a_course, $a_entity, $a_xml)
    {
        $this->final_processing_info[] = array(
            'course' => $a_course,
            'entity' => $a_entity,
            'xml' => $a_xml
        );
    }
}
