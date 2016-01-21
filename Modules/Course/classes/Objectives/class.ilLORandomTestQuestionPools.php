<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once './Modules/Course/classes/Objectives/class.ilLOSettings.php';

/**
* Class ilLOEditorGUI
*
* @author Stefan Meyer <smeyer.ilias@gmx.de> 
* $Id$
*
*
*/
class ilLORandomTestQuestionPools
{
	protected $container_id = 0;
	protected $objective_id = 0;
	protected $test_type = 0;
	protected $test_id = 0;
	protected $qpl_seq = 0;
	protected $limit = 50;
	

	/**
	 * Constructor
	 * @param type $a_container_id
	 * @param type $a_objective_id
	 */
	public function __construct($a_container_id, $a_objective_id, $a_test_type)
	{
		$this->container_id = $a_container_id;
		$this->objective_id = $a_objective_id;
		$this->test_type = $a_test_type;
		
		$this->read();
	}
	
	public static function lookupLimit($a_container_id, $a_objective_id, $a_test_type)
	{
		global $ilDB;
		
		$query = 'SELECT * FROM loc_rnd_qpl '.
				'WHERE container_id = '.$ilDB->quote($a_container_id,'integer').' '.
				'AND objective_id = '.$ilDB->quote($a_objective_id,'integer').' '.
				'AND tst_type = '.$ilDB->quote($a_test_type,'integer');
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->percentage;
		}
		return 0;
	}
	
	public static function lookupSequence($a_container_id, $a_objective_id, $a_test_id)
	{
		global $ilDB;
		
		$query = 'SELECT * FROM loc_rnd_qpl '.
				'WHERE container_id = '.$ilDB->quote($a_container_id,'integer').' '.
				'AND objective_id = '.$ilDB->quote($a_objective_id,'integer').' '.
				'AND tst_id = '.$ilDB->quote($a_test_id,'integer');
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->qp_seq;
		}
		return 0;
		
	}
	
	/**
	 * Lookup objective id by sequence
	 */
	public static function lookupObjectiveIdsBySequence($a_container_id, $a_seq_id)
	{
		global $ilDB;
		
		$query = 'SELECT objective_id FROM loc_rnd_qpl '.
				'WHERE container_id = '.$ilDB->quote($a_container_id,'integer').' '.
				'AND qp_seq = '.$ilDB->quote($a_seq_id,'integer');
		$res = $ilDB->query($query);
		$objectiveIds = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$objectiveIds[] = $row->objective_id;
		}
		return $objectiveIds;
	}


	public function setContainerId($a_id)
	{
		$this->container_id = $a_id;
	}
	
	public function getContainerId()
	{
		return $this->container_id;
	}
	
	public function setObjectiveId($a_id)
	{
		$this->objective_id = $a_id;
	}
	
	public function getObjectiveId()
	{
		return $this->objective_id;
	}
	
	public function setTestType($a_type)
	{
		$this->test_type = $a_type;
	}
	
	public function getTestType()
	{
		return $this->test_type;
	}
	
	public function setTestId($a_id)
	{
		$this->test_id = $a_id;
	}
	
	public function getTestId()
	{
		return $this->test_id;
	}
	
	public function setQplSequence($a_id)
	{
		$this->qpl_seq = $a_id;
	}
	
	public function getQplSequence()
	{
		return $this->qpl_seq;
	}
	
	public function setLimit($a_id)
	{
		$this->limit = $a_id;
	}
	
	public function getLimit()
	{
		return $this->limit;
	}
	
	/**
	 * Copy assignment
	 * @param type $a_copy_id
	 * @param type $a_new_objective_id
	 */
	public function copy($a_copy_id, $a_new_course_id, $a_new_objective_id)
	{
		include_once './Services/CopyWizard/classes/class.ilCopyWizardOptions.php';
		$options = ilCopyWizardOptions::_getInstance($a_copy_id);
		$mappings = $options->getMappings();
		
		$new_ass = new self(
			$a_new_course_id,
			$a_new_objective_id,
			$this->getTestType()
		);
		$new_ass->setLimit($this->getLimit());

		$mapped_id = 0;
		$test_ref_id = 0;
		foreach((array) ilObject::_getAllReferences($this->getTestId()) as $tmp => $ref_id)
		{
			ilLoggerFactory::getLogger('crs')->debug($tmp .' ' . $ref_id);
			$test_ref_id = $ref_id;
			$mapped_id = $mappings[$ref_id];
			if($mapped_id)
			{
				continue;
			}
		}
		
		if(!$mapped_id)
		{
			ilLoggerFactory::getLogger('crs')->debug('No test mapping found for random question pool assignment: ' . $this->getTestId());
			return FALSE;
		}
		$new_ass->setTestId($mapped_id);
		
		// Mapping for sequence
		$new_question_info = $mappings[$test_ref_id.'_rndSelDef_'.$this->getQplSequence()];
		$new_question_arr = explode('_',$new_question_info);
		if(!isset($new_question_arr[2]) or !$new_question_arr[2])
		{
			ilLoggerFactory::getLogger('crs')->debug(print_r($mappings,TRUE));
			ilLoggerFactory::getLogger('crs')->debug('Found invalid or no mapping format of random question id mapping: ' . print_r($new_question_arr,TRUE));
			return FALSE;
		}
		
		$new_ass->setQplSequence($new_question_arr[2]);
		$new_ass->create();
	}
	
	
	public function read()
	{
		global $ilDB;
		
		$query = 'SELECT * FROM loc_rnd_qpl '.
				'WHERE container_id = '.$ilDB->quote($this->getContainerId(),'integer').' '.
				'AND objective_id = '.$ilDB->quote($this->getObjectiveId(),'integer').' '.
				'AND tst_type = '.$ilDB->quote($this->getTestType(),'integer');
		
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->setLimit($row->percentage);
			$this->setTestId($row->tst_id);
			$this->setQplSequence($row->qp_seq);
		}
		return true;
	}
	
	public function delete()
	{
		global $ilDB;

		$query = 'DELETE FROM loc_rnd_qpl '.
				'WHERE container_id = '.$ilDB->quote($this->getContainerId(),'integer').' '.
				'AND objective_id = '.$ilDB->quote($this->getObjectiveId(),'integer').' '.
				'AND tst_type = '.$ilDB->quote($this->getTestType(),'integer');
		$ilDB->manipulate($query);
	}
	
	public function create()
	{
		global $ilDB;
		
		$query = 'INSERT INTO loc_rnd_qpl ' .
				'(container_id, objective_id, tst_type, tst_id, qp_seq, percentage) '.
				'VALUES ( '.
				$ilDB->quote($this->getContainerId(),'integer').', '.
				$ilDB->quote($this->getObjectiveId(),'integer').', '.
				$ilDB->quote($this->getTestType(),'integer').', '.
				$ilDB->quote($this->getTestId(),'integer').', '.
				$ilDB->quote($this->getQplSequence(),'integer').', '.
				$ilDB->quote($this->getLimit()).' '.
				')';
		$ilDB->manipulate($query);
	}
}
?>
