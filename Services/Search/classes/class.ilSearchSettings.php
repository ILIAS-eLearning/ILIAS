<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilObjSearchSettingsGUI
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* @extends ilObjectGUI
* @package ilias-core
*/

class ilSearchSettings
{
	const LIKE_SEARCH = 0;
	const INDEX_SEARCH = 1;
	const LUCENE_SEARCH = 2;
	
	const OPERATOR_AND	= 1;
	const OPERATOR_OR	= 2;
	
	protected static $instance = null;
	
	protected $default_operator = self::OPERATOR_AND;
	protected $fragmentSize = 30;
	protected $fragmentCount =  3;
	protected $numSubitems = 5;
	protected $showRelevance = true;
	protected $last_index_date = null;
	// END PATCH Lucene Search
	
	var $ilias = null;
	var $max_hits = null;
	var $index = null;

	function ilSearchSettings()
	{
		global $ilias;

		$this->ilias =& $ilias;
		$this->__read();
	}
	
	/**
	 * 
	 *
	 * @return
	 * @static
	 */
	public static function getInstance()
	{
		if(self::$instance == null)
		{
			return self::$instance = new ilSearchSettings();
		}
		return self::$instance;
	}

	/**
	* Read the ref_id of Search Settings object. normally used for rbacsystem->checkAccess()
	* @return int ref_id
	* @access	public
	*/
	function _getSearchSettingRefId()
	{
		global $ilDB;

		static $seas_ref_id = 0;

		if($seas_ref_id)
		{
			return $seas_ref_id;
		}
		$query = "SELECT object_reference.ref_id as ref_id FROM object_reference,tree,object_data ".
			"WHERE tree.parent = ".$ilDB->quote(SYSTEM_FOLDER_ID,'integer')." ".
			"AND object_data.type = 'seas' ".
			"AND object_reference.ref_id = tree.child ".
			"AND object_reference.obj_id = object_data.obj_id";
			
		$res = $ilDB->query($query);
		$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
		
		return $seas_ref_id = $row->ref_id;
	}

	function enabledIndex()
	{
		return $this->index ? true : false;
	}
	function enableIndex($a_status)
	{
		$this->index = $a_status;
	}
	function enabledLucene()
	{
		return $this->lucene ? true : false;
	}
	function enableLucene($a_status)
	{
		$this->lucene = $a_status ? true : false;
	}

	function getMaxHits()
	{
		return $this->max_hits;
	}
	function setMaxHits($a_max_hits)
	{
		$this->max_hits = $a_max_hits;
	}
	
	// BEGIN PATCH Lucene search
	public function getDefaultOperator()
	{
		return $this->default_operator;
	}
	
	public function setDefaultOperator($a_op)
	{
		$this->default_operator = $a_op;
	}
	
	public function setFragmentSize($a_size)
	{
		$this->fragmentSize = $a_size;
	}
	
	public function getFragmentSize()
	{
		return $this->fragmentSize;
	}
	
	public function setFragmentCount($a_count)
	{
		$this->fragmentCount = $a_count;
	}

	function getHideAdvancedSearch()
	{
		return $this->hide_adv_search ? true : false;
	}
	function setHideAdvancedSearch($a_status)
	{
		$this->hide_adv_search = $a_status;
	}
	function getAutoCompleteLength()
	{
		return $this->auto_complete_length;
	}
	function setAutoCompleteLength($auto_complete_length)
	{
		$this->auto_complete_length = $auto_complete_length;
	}

	public function getFragmentCount()
	{
		return $this->fragmentCount;
	}
	
	public function setMaxSubitems($a_max)
	{
		$this->numSubitems = $a_max;
	}
	
	public function getMaxSubitems()
	{
		return $this->numSubitems;
	}
	
	public function isRelevanceVisible()
	{
		return $this->showRelevance;
	}
	
	public function showRelevance($a_status)
	{
		$this->showRelevance = (bool) $a_status;
	}
	
	public function getLastIndexTime()
	{
		return $this->last_index_date instanceof ilDateTime  ?
			$this->last_index_date :
			new ilDateTime('2009-01-01 12:00:00',IL_CAL_DATETIME);
	}
	
	/**
	 * @param object instance of ilDateTime 
	 */
	public function setLastIndexTime($time)
	{
		$this->last_index_date = $time;
	}
	// END PATCH Lucene Search
	
	function update()
	{
		// setSetting writes to db
		$this->ilias->setSetting('search_max_hits',$this->getMaxHits());
		$this->ilias->setSetting('search_index',(int) $this->enabledIndex());
		$this->ilias->setSetting('search_lucene',(int) $this->enabledLucene());
		
		// BEGIN PATCH Lucene search
		$this->ilias->setSetting('lucene_default_operator',$this->getDefaultOperator());
		$this->ilias->setSetting('lucene_fragment_size',$this->getFragmentSize());
		$this->ilias->setSetting('lucene_fragment_count',$this->getFragmentCount());
		$this->ilias->setSetting('lucene_max_subitems',$this->getMaxSubitems());
		$this->ilias->setSetting('lucene_show_relevance',$this->isRelevanceVisible());
		$this->ilias->setSetting('lucene_last_index_time',$this->getLastIndexTime()->get(IL_CAL_UNIX));
		// END PATCH Lucene Search
		$this->ilias->setSetting('hide_adv_search',(int) $this->getHideAdvancedSearch());
		$this->ilias->setSetting('auto_complete_length',(int) $this->getAutoCompleteLength());

		return true;
	}

	// PRIVATE
	function __read()
	{
		$this->setMaxHits($this->ilias->getSetting('search_max_hits',50));
		$this->enableIndex($this->ilias->getSetting('search_index',0));
		$this->enableLucene($this->ilias->getSetting('search_lucene',0));
		
		// BEGIN PATCH Lucene search
		$this->setDefaultOperator($this->ilias->getSetting('lucene_default_operator',self::OPERATOR_AND));
		$this->setFragmentSize($this->ilias->getSetting('lucene_fragment_size',30));
		$this->setFragmentCount($this->ilias->getSetting('lucene_fragment_count',3));
		$this->setMaxSubitems($this->ilias->getSetting('lucene_max_subitems',5));
		$this->showRelevance($this->ilias->getSetting('lucene_show_relevance',true));

		if($time = $this->ilias->getSetting('lucene_last_index_time',false))
		{
			$this->setLastIndexTime(new ilDateTime($time,IL_CAL_UNIX));
		}
		else
		{
			$this->setLastIndexTime(null);	
		}
		// END PATCH Lucene Search
		
		$this->setHideAdvancedSearch($this->ilias->getSetting('hide_adv_search',0));
		$this->setAutoCompleteLength($this->ilias->getSetting('auto_complete_length',10));
	}
}
?>