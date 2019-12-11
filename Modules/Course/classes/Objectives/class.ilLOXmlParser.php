<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Class ilLOXmlWriter
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
*
*
*/
class ilLOXmlParser
{
    const TYPE_TST_PO = 1;
    const TYPE_TST_ALL = 2;
    const TYPE_TST_RND = 3;
    
    
    private $xml = '';
    private $course = null;
    private $mapping = null;
    
    /**
     * Constructor
     * @param ilObjCourse $course
     * @param type $a_xml
     */
    public function __construct(ilObjCourse $course, $a_xml)
    {
        $this->course = $course;
        $this->xml = $a_xml;
    }
    
    /**
     * Set import mapping
     * @param ilImportMapping $mapping
     */
    public function setMapping(ilImportMapping $mapping)
    {
        $this->mapping = $mapping;
    }
    
    /**
     * Get import mapping
     * @return ilImportMapping
     */
    public function getMapping()
    {
        return $this->mapping;
    }
    
    /**
     * Get course
     * @return ilObjCourse
     */
    protected function getCourse()
    {
        return $this->course;
    }
    
    /**
     * Parse xml
     */
    public function parse()
    {
        libxml_use_internal_errors(true);
        $root = simplexml_load_string(trim($this->xml));
        if (!$root instanceof SimpleXMLElement) {
            $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': XML is: ' . $this->xml . (string) $root);
            $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': Error parsing objective xml: ' . $this->parseXmlErrors());
            return false;
        }
        $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': Handling element: ' . (string) $root->getName());
        $this->parseSettings($root);
        $this->parseObjectives($root);
    }
    
    /**
     * Parse object dependencies (assigned strucure objects, page objects, fixed questions)
     */
    public function parseObjectDependencies()
    {
        libxml_use_internal_errors(true);
        $root = simplexml_load_string(trim($this->xml));
        if (!$root instanceof SimpleXMLElement) {
            $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': XML is: ' . $this->xml . (string) $root);
            $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': Error parsing objective xml: ' . $this->parseXmlErrors());
            return false;
        }
        
        foreach ($root->Objective as $obj) {
            $mapped_objective_id = $this->getMapping()->getMapping('Modules/Course', 'objectives', (string) $obj->attributes()->id);
            if ($mapped_objective_id) {
                $this->parseMaterials($obj, $mapped_objective_id);
                $this->parseTests($obj, $mapped_objective_id);
            }
        }
    }
    
    /**
     *
     * @param SimpleXMLElement $root
     */
    protected function parseSettings(SimpleXMLElement $root)
    {
        include_once './Modules/Course/classes/Objectives/class.ilLOSettings.php';
        $settings = ilLOSettings::getInstanceByObjId($this->getCourse()->getId());
        $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': Handling element: ' . (string) $root->Settings->getName());
        foreach ($root->Settings as $set) {
            $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': Handling element: ' . (string) $set->getName());
            $settings->setInitialTestType((int) (string) $set->attributes()->initialTestType);
            $settings->setInitialTestAsStart((bool) (string) $set->attributes()->initialTestStart);
            $settings->setQualifyingTestType((int) (string) $set->attributes()->qualifyingTestType);
            $settings->setQualifyingTestAsStart((bool) (string) $set->attributes()->qualifyingTestStart);
            $settings->resetResults((bool) (string) $set->attributes()->resetResults);
            $settings->setPassedObjectiveMode((int) (string) $set->attributes()->passedObjectivesMode);
            
            // itest
            $itest = (int) $this->getMappingInfoForItem((int) (string) $set->attributes()->iTest);
            $settings->setInitialTest($itest);
            
            // qtest
            $qtest = (int) $this->getMappingInfoForItem((int) (string) $set->attributes()->qTest);
            $settings->setQualifiedTest($qtest);
            
            $settings->update();
        }
    }
    
    /**
     * Parse objective
     * @param SimpleXMLElement $root
     */
    protected function parseObjectives(SimpleXMLElement $root)
    {
        foreach ($root->Objective as $obj) {
            include_once './Modules/Course/classes/class.ilCourseObjective.php';
            $new_obj = new ilCourseObjective($this->getCourse());
            $new_obj->setActive((bool) (string) $obj->attributes()->online);
            $new_obj->setTitle((string) $obj->Title);
            $new_obj->setDescription((string) $obj->Description);
            $new_obj->setPosition((int) (string) $obj->attributes()->position);
            $new_objective_id = $new_obj->add();
            
            $this->getMapping()->addMapping('Modules/Course', 'objectives', (string) $obj->attributes()->id, $new_objective_id);
            $this->getMapping()->addMapping('Services/COPage', 'pg', 'lobj:' . (string) $obj->attributes()->id, 'lobj:' . $new_objective_id);
            
            // done after container import complete
            //$this->parseMaterials($obj,$new_objective_id);
            //$this->parseTests($obj, $new_objective_id);
        }
    }
    
    /**
     * Parse assigned materials
     * @param SimpleXMLElement $obj
     */
    protected function parseMaterials(SimpleXMLElement $obj, $a_objective_id)
    {
        foreach ($obj->Material as $mat) {
            $mat_ref_id = (string) $mat->attributes()->refId;
            
            $mapping_ref_id = $this->getMappingInfoForItem($mat_ref_id);
            if ($mapping_ref_id) {
                include_once './Modules/Course/classes/class.ilCourseObjectiveMaterials.php';
                $new_mat = new ilCourseObjectiveMaterials($a_objective_id);
                $new_mat->setLMRefId($mapping_ref_id);
                
                $mat_type = (string) $mat->attributes()->type;
                $obj_id = 0;
                switch ($mat_type) {
                    case 'st':
                        $mapped_chapter = $this->getMapping()->getMapping(
                            'Modules/LearningModule',
                            'lm_tree',
                            (int) (string) $mat->attributes()->objId
                        );
                        if ($mapped_chapter) {
                            $obj_id = $mapped_chapter;
                        }
                        break;
                    
                    case 'pg':
                        $mapped_page = $this->getMapping()->getMapping(
                            'Modules/LearningModule',
                            'pg',
                            (int) (string) $mat->attributes()->objId
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
                    $new_id  = $new_mat->add();
                    $new_mat->writePosition($new_id, (int) (string) $mat->attributes()->position);
                }
            }
        }
    }
    
    /**
     * Parse tests of objective
     * @param SimpleXMLElement $obj
     * @param type $a_objective_id
     */
    protected function parseTests(SimpleXMLElement $obj, $a_objective_id)
    {
        $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': Parsing ' . (string) $obj->getName());
        
        foreach ($obj->Test as $tst) {
            $type = (int) (string) $tst->attributes()->type;


            if ($type == self::TYPE_TST_PO) {
                $tst_ref_id = (string) $tst->attributes()->refId;
                $mapping_ref_id = $this->getMappingInfoForItem($tst_ref_id);
                $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': Found test ref id ' . (string) $tst_ref_id);
                if (!$mapping_ref_id) {
                    continue;
                }
                include_once './Modules/Course/classes/Objectives/class.ilLOTestAssignment.php';
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
                    (string) $new_qpl_id
                );
                $rnd->setTestId($mapping_id);
                $rnd->setLimit((string) $tst->attributes()->limit);
                $rnd->create();
            } else {
                $tst_ref_id = (string) $tst->attributes()->refId;
                $mapping_ref_id = $this->getMappingInfoForItem($tst_ref_id);
                if (!$mapping_ref_id) {
                    continue;
                }
                include_once './Modules/Course/classes/class.ilCourseObjectiveQuestion.php';
                $quest = new ilCourseObjectiveQuestion($a_objective_id);
                $quest->setTestRefId($mapping_ref_id);
                $quest->setTestObjId(ilObject::_lookupObjId($mapping_ref_id));
                $quest->setTestStatus((string) $tst->attributes()->testType);
                $quest->setTestSuggestedLimit((string) $tst->attributes()->limit);
                
                foreach ($tst->Question as $qst) {
                    $qid = (string) $qst->attributes()->id;
                    $mapping_qid = $this->getMappingForQuestion($qid);
                    if ($mapping_qid) {
                        $quest->setQuestionId($mapping_qid);
                        $quest->add();
                    }
                }
            }
        }
    }
    
    /**
     * Get mapping info
     * @param type $a_ref_id
     * @param type $a_obj_id
     * @return int ref id of mapped item
     */
    protected function getMappingInfoForItem($a_ref_id)
    {
        $new_ref_id = $this->getMapping()->getMapping('Services/Container', 'refs', $a_ref_id);
        $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': Found new ref_id: ' . $new_ref_id . ' for ' . $a_ref_id);
        return (int) $new_ref_id;
    }
    
    /**
     * Get obj_id mapping
     * @param int $a_obj_id
     * @return int
     */
    protected function getMappingInfoForItemObject($a_obj_id)
    {
        $new_obj_id = $this->getMapping()->getMapping('Services/Container', 'objs', $a_obj_id);
        $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': Found new ref_id: ' . $new_obj_id . ' for ' . $a_obj_id);
        return (int) $new_obj_id;
    }
    
    protected function getMappingForQuestion($qid)
    {
        $new_qid = $this->getMapping()->getMapping('Modules/Test', 'quest', $qid);
        $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': Found new question_id: ' . $new_qid . ' for ' . $qid);
        return $new_qid;
    }
    
    protected function getMappingForQpls($a_id)
    {
        $new_id = $this->getMapping()->getMapping('Modules/Test', 'rnd_src_pool_def', $a_id);
        if ($new_id) {
            return $new_id;
        }
        return 0;
    }








    /**
     * Parse xml errors from libxml_get_errors
     *
     * @return string
     */
    protected function parseXmlErrors()
    {
        $errors = '';
        foreach (libxml_get_errors() as $err) {
            $errors .= $err->code . '<br/>';
        }
        return $errors;
    }
}
