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
 * Class ilLOXmlWriter
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilLOXmlParser
{
    public const TYPE_TST_PO = 1;
    public const TYPE_TST_ALL = 2;
    public const TYPE_TST_RND = 3;

    private string $xml = '';
    private ilObjCourse $course;
    private ?ilImportMapping $mapping = null;

    private ilLogger $logger;

    public function __construct(ilObjCourse $course, string $a_xml)
    {
        global $DIC;

        $this->logger = $DIC->logger()->crs();
        $this->course = $course;
        $this->xml = $a_xml;
    }

    public function setMapping(ilImportMapping $mapping): void
    {
        $this->mapping = $mapping;
    }

    public function getMapping(): ?ilImportMapping
    {
        return $this->mapping;
    }

    protected function getCourse(): ilObjCourse
    {
        return $this->course;
    }

    public function parse(): void
    {
        $use_internal_errors = libxml_use_internal_errors(true);
        $root = simplexml_load_string(trim($this->xml));
        libxml_use_internal_errors($use_internal_errors);
        if (!$root instanceof SimpleXMLElement) {
            $this->logger->debug('XML is: ' . $this->xml . $root);
            $this->logger->debug('Error parsing objective xml: ' . $this->parseXmlErrors());
            return;
        }
        $this->logger->debug('Handling element: ' . $root->getName());
        $this->parseSettings($root);
        $this->parseObjectives($root);
    }

    /**
     * Parse object dependencies (assigned strucure objects, page objects, fixed questions)
     */
    public function parseObjectDependencies(): void
    {
        $use_internal_errors = libxml_use_internal_errors(true);
        $root = simplexml_load_string(trim($this->xml));
        libxml_use_internal_errors($use_internal_errors);
        if (!$root instanceof SimpleXMLElement) {
            $this->logger->debug('XML is: ' . $this->xml . $root);
            $this->logger->debug('Error parsing objective xml: ' . $this->parseXmlErrors());
            return;
        }

        foreach ($root->Objective as $obj) {
            $mapped_objective_id = $this->getMapping()->getMapping(
                'Modules/Course',
                'objectives',
                (string) $obj->attributes()->id
            );
            if ($mapped_objective_id) {
                $this->parseMaterials($obj, (int) $mapped_objective_id);
                $this->parseTests($obj, (int) $mapped_objective_id);
            }
        }
    }

    protected function parseSettings(SimpleXMLElement $root): void
    {
        $settings = ilLOSettings::getInstanceByObjId($this->getCourse()->getId());
        $this->logger->debug(': Handling element: ' . $root->Settings->getName());
        foreach ($root->Settings as $set) {
            $this->logger->debug('Handling element: ' . $set->getName());
            $settings->setInitialTestType((int) (string) $set->attributes()->initialTestType);
            $settings->setInitialTestAsStart((bool) (string) $set->attributes()->initialTestStart);
            $settings->setQualifyingTestType((int) (string) $set->attributes()->qualifyingTestType);
            $settings->setQualifyingTestAsStart((bool) (string) $set->attributes()->qualifyingTestStart);
            $settings->resetResults((bool) (string) $set->attributes()->resetResults);
            $settings->setPassedObjectiveMode((int) (string) $set->attributes()->passedObjectivesMode);

            // itest
            $itest = $this->getMappingInfoForItem((int) (string) $set->attributes()->iTest);
            $settings->setInitialTest($itest);

            // qtest
            $qtest = $this->getMappingInfoForItem((int) (string) $set->attributes()->qTest);
            $settings->setQualifiedTest($qtest);

            $settings->update();
        }
    }

    /**
     * Parse objective
     */
    protected function parseObjectives(SimpleXMLElement $root): void
    {
        foreach ($root->Objective as $obj) {
            $new_obj = new ilCourseObjective($this->getCourse());
            $new_obj->setActive((bool) (string) $obj->attributes()->online);
            $new_obj->setTitle((string) $obj->Title);
            $new_obj->setDescription((string) $obj->Description);
            $new_obj->setPosition((int) (string) $obj->attributes()->position);
            $new_objective_id = $new_obj->add();

            $this->getMapping()->addMapping(
                'Modules/Course',
                'objectives',
                (string) $obj->attributes()->id,
                (string) $new_objective_id
            );
            $this->getMapping()->addMapping(
                'Services/COPage',
                'pg',
                'lobj:' . $obj->attributes()->id,
                'lobj:' . $new_objective_id
            );
        }
    }

    protected function parseMaterials(SimpleXMLElement $obj, int $a_objective_id): void
    {
        foreach ($obj->Material as $mat) {
            $mat_ref_id = (string) $mat->attributes()->refId;

            $mapping_ref_id = $this->getMappingInfoForItem((int) $mat_ref_id);
            if ($mapping_ref_id) {
                $new_mat = new ilCourseObjectiveMaterials($a_objective_id);
                $new_mat->setLMRefId($mapping_ref_id);

                $mat_type = (string) $mat->attributes()->type;
                $obj_id = 0;
                switch ($mat_type) {
                    case 'st':
                        $mapped_chapter = $this->getMapping()->getMapping(
                            'Modules/LearningModule',
                            'lm_tree',
                            (string) $mat->attributes()->objId
                        );
                        if ($mapped_chapter) {
                            $obj_id = $mapped_chapter;
                        }
                        break;

                    case 'pg':
                        $mapped_page = $this->getMapping()->getMapping(
                            'Modules/LearningModule',
                            'pg',
                            (string) $mat->attributes()->objId
                        );
                        if ($mapped_page) {
                            $obj_id = $mapped_page;
                        }
                        break;

                    default:
                        $obj_id = ilObject::_lookupObjId($mapping_ref_id);
                        break;
                }
                if ($obj_id) {
                    $new_mat->setLMObjId($obj_id);
                    $new_mat->setType((string) $mat->attributes()->type);
                    $new_id = $new_mat->add();
                    $new_mat->writePosition($new_id, (int) (string) $mat->attributes()->position);
                }
            }
        }
    }

    protected function parseTests(SimpleXMLElement $obj, int $a_objective_id): void
    {
        $this->logger->debug(': Parsing ' . $obj->getName());

        foreach ($obj->Test as $tst) {
            $type = (int) (string) $tst->attributes()->type;
            if ($type == self::TYPE_TST_PO) {
                $tst_ref_id = (string) $tst->attributes()->refId;
                $mapping_ref_id = $this->getMappingInfoForItem((int) $tst_ref_id);
                $this->logger->debug('Found test ref id ' . $tst_ref_id);
                if (!$mapping_ref_id) {
                    continue;
                }
                $assignment = new ilLOTestAssignment();
                $assignment->setContainerId($this->getCourse()->getId());
                $assignment->setTestRefId($mapping_ref_id);
                $assignment->setObjectiveId($a_objective_id);
                $assignment->setAssignmentType((int) (string) $tst->attributes()->testType);
                $assignment->save();
            } elseif ($type == self::TYPE_TST_RND) {
                $tst_obj_id = (int) (string) $tst->attributes()->objId;
                $mapping_id = $this->getMappingInfoForItemObject($tst_obj_id);
                if (!$mapping_id) {
                    continue;
                }

                $new_qpl_id = $this->getMappingForQpls((int) (string) $tst->attributes()->poolId);
                if (!$new_qpl_id) {
                    continue;
                }

                $rnd = new ilLORandomTestQuestionPools(
                    $this->getCourse()->getId(),
                    $a_objective_id,
                    (int) (string) $tst->attributes()->testType,
                    $new_qpl_id
                );
                $rnd->setTestId($mapping_id);
                $rnd->setLimit((int) $tst->attributes()->limit);
                $rnd->create();
            } else {
                $tst_ref_id = (string) $tst->attributes()->refId;
                $mapping_ref_id = $this->getMappingInfoForItem((int) $tst_ref_id);
                if (!$mapping_ref_id) {
                    continue;
                }
                $quest = new ilCourseObjectiveQuestion($a_objective_id);
                $quest->setTestRefId($mapping_ref_id);
                $quest->setTestObjId(ilObject::_lookupObjId($mapping_ref_id));
                $quest->setTestStatus((int) $tst->attributes()->testType);
                $quest->setTestSuggestedLimit((int) $tst->attributes()->limit);

                foreach ($tst->Question as $qst) {
                    $qid = (string) $qst->attributes()->id;
                    $mapping_qid = $this->getMappingForQuestion((int) $qid);
                    if ($mapping_qid) {
                        $quest->setQuestionId($mapping_qid);
                        $quest->add();
                    }
                }
            }
        }
    }

    protected function getMappingInfoForItem(int $a_ref_id): int
    {
        $new_ref_id = $this->getMapping()->getMapping('Services/Container', 'refs', (string) $a_ref_id);
        $this->logger->debug(': Found new ref_id: ' . $new_ref_id . ' for ' . $a_ref_id);
        return (int) $new_ref_id;
    }

    protected function getMappingInfoForItemObject(int $a_obj_id): int
    {
        $new_obj_id = $this->getMapping()->getMapping('Services/Container', 'objs', (string) $a_obj_id);
        $this->logger->debug('Found new ref_id: ' . $new_obj_id . ' for ' . $a_obj_id);
        return (int) $new_obj_id;
    }

    protected function getMappingForQuestion(int $qid): int
    {
        $new_qid = $this->getMapping()->getMapping('Modules/Test', 'quest', (string) $qid);
        $this->logger->debug('Found new question_id: ' . $new_qid . ' for ' . $qid);
        return (int) $new_qid;
    }

    protected function getMappingForQpls(int $a_id): int
    {
        $new_id = $this->getMapping()->getMapping('Modules/Test', 'rnd_src_pool_def', (string) $a_id);
        if ($new_id) {
            return (int) $new_id;
        }
        return 0;
    }

    /**
     * Parse xml errors from libxml_get_errors
     */
    protected function parseXmlErrors(): string
    {
        $errors = '';
        foreach (libxml_get_errors() as $err) {
            $errors .= $err->code . '<br/>';
        }
        return $errors;
    }
}
