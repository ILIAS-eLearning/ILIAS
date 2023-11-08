<?php

declare(strict_types=0);
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
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup components\ILIASCourse
 */
class ilCourseImporter extends ilXmlImporter
{
    public const ENTITY_MAIN = 'crs';
    public const ENTITY_OBJECTIVE = 'objectives';

    private ?ilObjCourse $course = null;
    private array $final_processing_info = [];

    protected ilLogger $logger;

    public function __construct()
    {
        global $DIC;

        $this->logger = $DIC->logger()->crs();
    }

    public function init(): void
    {
    }

    /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
    public function importXmlRepresentation(
        string $a_entity,
        string $a_id,
        string $a_xml,
        ilImportMapping $a_mapping
    ): void {
        if ($new_id = $a_mapping->getMapping('components/ILIAS/Container', 'objs', $a_id)) {
            $refs = ilObject::_getAllReferences((int) $new_id);
            $this->course = ilObjectFactory::getInstanceByRefId((int) end($refs), false);
        } // Mapping for containers without subitems
        elseif ($new_id = $a_mapping->getMapping('components/ILIAS/Container', 'refs', '0')) {
            $this->course = ilObjectFactory::getInstanceByRefId((int) $new_id, false);
        } elseif (!$this->course instanceof ilObjCourse) {
            $this->course = new ilObjCourse();
            $this->course->create();
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

            $a_mapping->addMapping('components/ILIAS/Course', 'crs', $a_id, (string) $this->course->getId());

            // workaround for ilImportContainer::createDummy which creates Metadata via create(true)
            $this->course->deleteMetaData();
            $a_mapping->addMapping(
                'components/ILIAS/MetaData',
                'md',
                $a_id . ':0:crs',
                $this->course->getId() . ':0:crs'
            );
        } catch (ilSaxParserException|Exception $e) {
            $this->logger->error('Parsing failed with message, "' . $e->getMessage() . '".');
        }
    }

    public function afterContainerImportProcessing(\ilImportMapping $mapping): void
    {
        foreach ($this->final_processing_info as $info) {
            // import learning objectives
            $parser = new ilLOXmlParser($info['course'], $info['xml']);
            $parser->setMapping($mapping);
            $parser->parseObjectDependencies();
            return;
        }
    }

    protected function addFinalProcessingInfo($a_course, $a_entity, $a_xml): void
    {
        $this->final_processing_info[] = array(
            'course' => $a_course,
            'entity' => $a_entity,
            'xml' => $a_xml
        );
    }
}
