<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 */
class ilTestRandomQuestionSetSourcePoolDefinition
{
    /**
     * global $ilDB object instance
     *
     * @var ilDBInterface
     */
    protected $db = null;
    
    /**
     * object instance of current test
     *
     * @var ilObjTest
     */
    protected $testOBJ = null;

    private $id = null;
    
    private $poolId = null;

    /** @var null|int */
    private $poolRefId = null;
    
    private $poolTitle = null;
    
    private $poolPath = null;
    
    private $poolQuestionCount = null;
    
    // fau: taxFilter/typeFilter - new class variables
    #private $originalFilterTaxId = null;
    
    #private $originalFilterTaxNodeId = null;

    #private $mappedFilterTaxId = null;

    #private $mappedFilterTaxNodeId = null;
    
    /**
     * @var array taxId => [nodeId, ...]
     */
    private $originalTaxonomyFilter = array();
    
    /**
     * @var array taxId => [nodeId, ...]
     */
    private $mappedTaxonomyFilter = array();
    
    /**
     * @var array
     */
    private $typeFilter = array();
    // fau.
    // fau.

    /**
     * @var array
     */
    private $lifecycleFilter = array();
    
    private $questionAmount = null;
    
    private $sequencePosition = null;
    
    public function __construct(ilDBInterface $db, ilObjTest $testOBJ)
    {
        $this->db = $db;
        $this->testOBJ = $testOBJ;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }
    
    public function setPoolId($poolId)
    {
        $this->poolId = $poolId;
    }
    
    public function getPoolId()
    {
        return $this->poolId;
    }

    public function getPoolRefId() : ?int
    {
        return $this->poolRefId;
    }

    public function setPoolRefId(?int $poolRefId) : void
    {
        $this->poolRefId = $poolRefId;
    }
    
    public function setPoolTitle($poolTitle)
    {
        $this->poolTitle = $poolTitle;
    }
    
    public function getPoolTitle()
    {
        return $this->poolTitle;
    }
    
    public function setPoolPath($poolPath)
    {
        $this->poolPath = $poolPath;
    }
    
    public function getPoolPath()
    {
        return $this->poolPath;
    }
    
    public function setPoolQuestionCount($poolQuestionCount)
    {
        $this->poolQuestionCount = $poolQuestionCount;
    }
    
    public function getPoolQuestionCount()
    {
        return $this->poolQuestionCount;
    }
    
    // fau: taxFilter/typeFilter - new setters/getters
    /**
     * get the original taxonomy filter conditions
     * @return array	taxId => [nodeId, ...]
     */
    public function getOriginalTaxonomyFilter()
    {
        return $this->originalTaxonomyFilter;
    }
    
    /**
     * set the original taxonomy filter condition
     * @param  array taxId => [nodeId, ...]
     */
    public function setOriginalTaxonomyFilter($filter = array())
    {
        $this->originalTaxonomyFilter = $filter;
    }
    
    /**
     * get the original taxonomy filter for insert into the database
     * @return null|string		serialized taxonomy filter
     */
    private function getOriginalTaxonomyFilterForDbValue()
    {
        // TODO-RND2017: migrate to separate table for common selections by e.g. statistics
        return empty($this->originalTaxonomyFilter) ? null : serialize($this->originalTaxonomyFilter);
    }
    
    /**
     * get the original taxonomy filter from database value
     * @param null|string		serialized taxonomy filter
     */
    private function setOriginalTaxonomyFilterFromDbValue($value)
    {
        // TODO-RND2017: migrate to separate table for common selections by e.g. statistics
        $this->originalTaxonomyFilter = empty($value) ? array() : unserialize($value);
    }
    
    /**
     * get the mapped taxonomy filter conditions
     * @return 	array	taxId => [nodeId, ...]
     */
    public function getMappedTaxonomyFilter()
    {
        return $this->mappedTaxonomyFilter;
    }
    
    /**
     * set the original taxonomy filter condition
     * @param array 	taxId => [nodeId, ...]
     */
    public function setMappedTaxonomyFilter($filter = array())
    {
        $this->mappedTaxonomyFilter = $filter;
    }
    
    /**
     * get the original taxonomy filter for insert into the database
     * @return null|string		serialized taxonomy filter
     */
    private function getMappedTaxonomyFilterForDbValue()
    {
        return empty($this->mappedTaxonomyFilter) ? null : serialize($this->mappedTaxonomyFilter);
    }
    
    /**
     * get the original taxonomy filter from database value
     * @param null|string		serialized taxonomy filter
     */
    private function setMappedTaxonomyFilterFromDbValue($value)
    {
        $this->mappedTaxonomyFilter = empty($value) ? array() : unserialize($value);
    }
    
    
    /**
     * set the mapped taxonomy filter from original by applying a keys map
     * @param ilQuestionPoolDuplicatedTaxonomiesKeysMap $taxonomiesKeysMap
     */
    public function mapTaxonomyFilter(ilQuestionPoolDuplicatedTaxonomiesKeysMap $taxonomiesKeysMap)
    {
        $this->mappedTaxonomyFilter = array();
        foreach ($this->originalTaxonomyFilter as $taxId => $nodeIds) {
            $mappedNodeIds = array();
            foreach ($nodeIds as $nodeId) {
                $mappedNodeIds[] = $taxonomiesKeysMap->getMappedTaxNodeId($nodeId);
            }
            $this->mappedTaxonomyFilter[$taxonomiesKeysMap->getMappedTaxonomyId($taxId)] = $mappedNodeIds;
        }
    }
    
    public function setTypeFilter($typeFilter = array())
    {
        $this->typeFilter = $typeFilter;
    }
    
    public function getTypeFilter()
    {
        return $this->typeFilter;
    }
    
    /**
     * get the question type filter for insert into the database
     * @return null|string		serialized type filter
     */
    private function getTypeFilterForDbValue()
    {
        return empty($this->typeFilter) ? null : serialize($this->typeFilter);
    }
    
    /**
     * get the question type filter from database value
     * @param null|string		serialized type filter
     */
    private function setTypeFilterFromDbValue($value)
    {
        $this->typeFilter = empty($value) ? array() : unserialize($value);
    }
    
    /**
     * @return array
     */
    public function getLifecycleFilter()
    {
        return $this->lifecycleFilter;
    }
    
    /**
     * @param array $lifecycleFilter
     */
    public function setLifecycleFilter($lifecycleFilter)
    {
        $this->lifecycleFilter = $lifecycleFilter;
    }
    
    /**
     * @return null|string		serialized lifecycle filter
     */
    public function getLifecycleFilterForDbValue()
    {
        return empty($this->lifecycleFilter) ? null : serialize($this->lifecycleFilter);
    }
    
    /**
     * @param null|string		serialized lifecycle filter
     */
    public function setLifecycleFilterFromDbValue($dbValue)
    {
        $this->lifecycleFilter = empty($dbValue) ? array() : unserialize($dbValue);
    }

    /**
     * Get the type filter as a list of type tags
     * @return string[]
     */
    public function getTypeFilterAsTypeTags() : array
    {
        $map = [];
        foreach (ilObjQuestionPool::_getQuestionTypes(true) as $row) {
            $map[$row['question_type_id']] = $row['type_tag'];
        }

        $tags = [];
        foreach ($this->typeFilter as $type_id) {
            if (isset($map[$type_id])) {
                $tags[] = $map[$type_id];
            }
        }

        return $tags;
    }

    /**
     * Set the type filter from a list of type tags
     * @param string[] $tags
     */
    public function setTypeFilterFromTypeTags(array $tags)
    {
        $map = [];
        foreach (ilObjQuestionPool::_getQuestionTypes(true) as $row) {
            $map[$row['type_tag']] = $row['question_type_id'];
        }

        $this->typeFilter = [];
        foreach ($tags as $type_tag) {
            if (isset($map[$type_tag])) {
                $this->typeFilter[] = $map[$type_tag];
            }
        }
    }


    /*
    public function setOriginalFilterTaxId($originalFilterTaxId)
    {
        $this->originalFilterTaxId = $originalFilterTaxId;
    }

    public function getOriginalFilterTaxId()
    {
        return $this->originalFilterTaxId;
    }

    public function setOriginalFilterTaxNodeId($originalFilterNodeId)
    {
        $this->originalFilterTaxNodeId = $originalFilterNodeId;
    }

    public function getOriginalFilterTaxNodeId()
    {
        return $this->originalFilterTaxNodeId;
    }

    public function setMappedFilterTaxId($mappedFilterTaxId)
    {
        $this->mappedFilterTaxId = $mappedFilterTaxId;
    }

    public function getMappedFilterTaxId()
    {
        return $this->mappedFilterTaxId;
    }

    public function setMappedFilterTaxNodeId($mappedFilterTaxNodeId)
    {
        $this->mappedFilterTaxNodeId = $mappedFilterTaxNodeId;
    }

    public function getMappedFilterTaxNodeId()
    {
        return $this->mappedFilterTaxNodeId;
    }
    */
    // fau.

    public function setQuestionAmount($questionAmount)
    {
        $this->questionAmount = $questionAmount;
    }
    
    public function getQuestionAmount()
    {
        return $this->questionAmount;
    }
    
    public function setSequencePosition($sequencePosition)
    {
        $this->sequencePosition = $sequencePosition;
    }
    
    public function getSequencePosition()
    {
        return $this->sequencePosition;
    }
    
    // -----------------------------------------------------------------------------------------------------------------
    
    /**
     * @param array $dataArray
     */
    public function initFromArray($dataArray)
    {
        foreach ($dataArray as $field => $value) {
            switch ($field) {
                case 'def_id':				$this->setId($value);						break;
                case 'pool_fi':				$this->setPoolId($value);					break;
                case 'pool_ref_id':         $this->setPoolRefId($value ? (int) $value : null); break;
                case 'pool_title':			$this->setPoolTitle($value);				break;
                case 'pool_path':			$this->setPoolPath($value);					break;
                case 'pool_quest_count':	$this->setPoolQuestionCount($value);		break;
                // fau: taxFilter - use new db fields
                #case 'origin_tax_fi':		$this->setOriginalFilterTaxId($value);		break;
                #case 'origin_node_fi':		$this->setOriginalFilterTaxNodeId($value);	break;
                #case 'mapped_tax_fi':		$this->setMappedFilterTaxId($value);		break;
                #case 'mapped_node_fi':		$this->setMappedFilterTaxNodeId($value);	break;
                case 'origin_tax_filter':	$this->setOriginalTaxonomyFilterFromDbValue($value);	break;
                case 'mapped_tax_filter':	$this->setMappedTaxonomyFilterFromDbValue($value);		break;
                case 'type_filter':			$this->setTypeFilterFromDbValue($value);	break;
                case 'lifecycle_filter':			$this->setLifecycleFilterFromDbValue($value);	break;
                // fau.
                case 'quest_amount':		$this->setQuestionAmount($value);			break;
                case 'sequence_pos':		$this->setSequencePosition($value);			break;
            }
        }
    }
    
    /**
     * @param integer $poolId
     * @return boolean
     */
    public function loadFromDb($id)
    {
        $res = $this->db->queryF(
            "SELECT * FROM tst_rnd_quest_set_qpls WHERE def_id = %s",
            array('integer'),
            array($id)
        );
        
        while ($row = $this->db->fetchAssoc($res)) {
            $this->initFromArray($row);
            
            return true;
        }
        
        return false;
    }

    public function saveToDb()
    {
        if ($this->getId()) {
            $this->updateDbRecord($this->testOBJ->getTestId());
        } else {
            $this->insertDbRecord($this->testOBJ->getTestId());
        }
    }

    public function cloneToDbForTestId($testId)
    {
        $this->insertDbRecord($testId);
    }

    public function deleteFromDb()
    {
        $this->db->manipulateF(
            "DELETE FROM tst_rnd_quest_set_qpls WHERE def_id = %s",
            array('integer'),
            array($this->getId())
        );
    }

    /**
     * @param $testId
     */
    private function updateDbRecord($testId)
    {
        $this->db->update(
            'tst_rnd_quest_set_qpls',
            array(
                'test_fi' => array('integer', $testId),
                'pool_fi' => array('integer', $this->getPoolId()),
                'pool_ref_id' => array('integer', $this->getPoolRefId()),
                'pool_title' => array('text', $this->getPoolTitle()),
                'pool_path' => array('text', $this->getPoolPath()),
                'pool_quest_count' => array('integer', $this->getPoolQuestionCount()),
                // fau: taxFilter/typeFilter - use new db fields
                #'origin_tax_fi' => array('integer', $this->getOriginalFilterTaxId()),
                #'origin_node_fi' => array('integer', $this->getOriginalFilterTaxNodeId()),
                #'mapped_tax_fi' => array('integer', $this->getMappedFilterTaxId()),
                #'mapped_node_fi' => array('integer', $this->getMappedFilterTaxNodeId()),
                'origin_tax_filter' => array('text', $this->getOriginalTaxonomyFilterForDbValue()),
                'mapped_tax_filter' => array('text', $this->getMappedTaxonomyFilterForDbValue()),
                'type_filter' => array('text', $this->getTypeFilterForDbValue()),
                'lifecycle_filter' => array('text', $this->getLifecycleFilterForDbValue()),
                // fau.
                'quest_amount' => array('integer', $this->getQuestionAmount()),
                'sequence_pos' => array('integer', $this->getSequencePosition())
            ),
            array(
                'def_id' => array('integer', $this->getId())
            )
        );
    }

    /**
     * @param $testId
     */
    private function insertDbRecord($testId)
    {
        $nextId = $this->db->nextId('tst_rnd_quest_set_qpls');

        $this->db->insert('tst_rnd_quest_set_qpls', array(
                'def_id' => array('integer', $nextId),
                'test_fi' => array('integer', $testId),
                'pool_fi' => array('integer', $this->getPoolId()),
                'pool_ref_id' => array('integer', $this->getPoolRefId()),
                'pool_title' => array('text', $this->getPoolTitle()),
                'pool_path' => array('text', $this->getPoolPath()),
                'pool_quest_count' => array('integer', $this->getPoolQuestionCount()),
                // fau: taxFilter/typeFilter - use new db fields
                #'origin_tax_fi' => array('integer', $this->getOriginalFilterTaxId()),
                #'origin_node_fi' => array('integer', $this->getOriginalFilterTaxNodeId()),
                #'mapped_tax_fi' => array('integer', $this->getMappedFilterTaxId()),
                #'mapped_node_fi' => array('integer', $this->getMappedFilterTaxNodeId()),
                'origin_tax_filter' => array('text', $this->getOriginalTaxonomyFilterForDbValue()),
                'mapped_tax_filter' => array('text', $this->getMappedTaxonomyFilterForDbValue()),
                'type_filter' => array('text', $this->getTypeFilterForDbValue()),
                'lifecycle_filter' => array('text', $this->getLifecycleFilterForDbValue()),
                // fau.
                'quest_amount' => array('integer', $this->getQuestionAmount()),
                'sequence_pos' => array('integer', $this->getSequencePosition())
        ));

        $this->setId($nextId);
    }
    
    // -----------------------------------------------------------------------------------------------------------------
    
    public function getPoolInfoLabel(ilLanguage $lng)
    {
        $pool_path = $this->getPoolPath();
        if (is_int($this->getPoolRefId()) && ilObject::_lookupObjId($this->getPoolRefId())) {
            $path = new ilPathGUI();
            $path->enableTextOnly(true);
            $pool_path = $path->getPath(ROOT_FOLDER_ID, $this->getPoolRefId());
        }

        $poolInfoLabel = sprintf(
            $lng->txt('tst_dynamic_question_set_source_questionpool_summary_string'),
            $this->getPoolTitle(),
            $pool_path,
            $this->getPoolQuestionCount()
        );
        
        return $poolInfoLabel;
    }

    // -----------------------------------------------------------------------------------------------------------------
}
