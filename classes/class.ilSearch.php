<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/


/**
* search
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version Id$
* 
*/
class ilSearch
{
	/**
	* ilias object
	* @var object DB
	* @access public
	*/	
	var $ilias;
	var $lng;
	var $rbacsystem;
	var $user_id;				// INTEGER USED FOR SAVED RESULTS
	var $search_string;			// INPUT FROM SEARCH FORM
	var $parsed_str;			// PARSED INPUT
	var $combination;			// STRING 'and' or 'or'
	var $min_word_length = 3;	// Define minimum character length for queries
	var $search_for;			// OBJECT TYPE 'usr','grp','lm','dbk'
	var $search_in;				// STRING SEARCH IN 'content' OR 'meta'
	var $search_type;			// STRING 'new' or 'result'
	var $result;				// RESULT SET array['object_type']['counter']
	var $perform_update;		// UPDATE USER SEARCH HISTORY default is true SEE function setPerformUpdate()
	var $read_db_result;		// READ db result true/false

	var $allow_empty_search;		// ALLOW EMPTY SEARCH TERM use setEmtySearch(true | false) TO SET THIS VALUE DEFAULT (FALSE)
	/**
	* Constructor
	* @access	public
	*/
	function ilSearch($a_user_id = 0,$a_read = false)
	{
		global $ilias,$rbacsystem,$lng;
		
		// Initiate variables
		$this->ilias =& $ilias;
		$this->lng =& $lng;
		$this->lng->loadLanguageModule("search");
		$this->rbacsystem =& $rbacsystem;
		$this->user_id = $a_user_id;

		$this->setPerformUpdate(true);
		$this->setEmptySearch(false);
		$this->read_db_result = $a_read;

		// READ OLD SEARCH RESULTS FROM DATABASE
		#$this->__readDBResult();
	}

	// SET METHODS
	function setSearchString($a_search_str)
	{
		$this->search_string = trim($a_search_str);
	}
	function setCombination($a_combination)
	{
		// 'and' or 'or'
		$this->combination = $a_combination;
	}
	function setSearchFor($a_search_for)
	{
		$this->search_for = $a_search_for;
	}
	function setSearchIn($a_search_in)
	{
		$this->search_in = $a_search_in;
	}
	function setResult($a_result)
	{
		$this->result = $a_result;
	}
	function setSearchType($a_type)
	{
		$this->search_type = $a_type;
	}
	function setPerformUpdate($a_value)
	{
		$this->perform_update = $a_value;
	}
	function setEmptySearch($a_value)
	{
		$this->allow_empty_search = $a_value;
	}

	function setMinWordLength($a_min_word_length)
	{
		$this->min_word_length = $a_min_word_length;
	}
	function getMinWordLength()
	{
		return $this->min_word_length;
	}
	
	// GET MEHODS
	function getUserId()
	{
		return $this->user_id;
	}
	function getSearchString()
	{
		return $this->search_string;
	}
	function getCombination()
	{
		return $this->combination ? $this->combination : "or";
	}
	function getSearchFor()
	{
		return $this->search_for ? $this->search_for : array();
	}
	function getSearchIn()
	{
		return $this->search_in ? $this->search_in : array();
	}
	function getSearchInByType($a_type)
	{
		if($a_type == 'lm' or $a_type == 'dbk')
		{
			return $this->search_in[$a_type];
		}
		else
		{
			return false;
		}
	}
	function getResults()
	{
		return $this->result ? $this->result : array();
	}
	function getResultByType($a_type)
	{
        return $this->result[$a_type] ? $this->result[$a_type] : array();
	}
	function getSearchType()
	{
		return $this->search_type;
	}
	function getPerformUpdate()
	{
		return $this->perform_update;
	}
	function getEmptySearch()
	{
		return $this->allow_empty_search;
	}


	// PUBLIC
	function getNumberOfResults()
	{
		$number = count($this->getResultByType("usr")) + count($this->getResultByType("grp")) + count($this->getResultByType("role"));

		$tmp_res = $this->getResultByType("dbk");
		$number += count($tmp_res["meta"]) + count($tmp_res["content"]);

		$tmp_res = $this->getResultByType("lm");
		$number += count($tmp_res["meta"]) + count($tmp_res["content"]);
					
		return $number;
	}

	function validate(&$message)
	{
		$ok = true;

		if(!$this->getEmptySearch())
		{
			if(!$this->getSearchString())
			{
				$message .= $this->lng->txt("search_no_search_term")."<br/>";
				$ok = false;
			}
			$this->__parseSearchString();

			if(!$this->__validateParsedString($message))
			{
				$ok = false;
			}
			if(!$this->getSearchFor())
			{
				$message .= $this->lng->txt("search_no_category")."<br/>";
				$ok = false;
			}
		}
		return $ok;
	}

	function performSearch()
	{
		global $objDefinition, $ilBench;

		$ilBench->start("Search", "performSearch");

		$result = array("usr" => array(),
						"grp" => array(),
						"lm"  => array(),
						"dbk" => array(),
						"role"=> array());

		foreach($this->getSearchFor() as $obj_type)
		{
			switch($obj_type)
			{
				case "usr":
					// TODO: NOT NICE BUT USEFUL
					// THIS VAR IS USED IN __getResultIdsByType()
					$this->act_type = 'usr';
					$result["usr"] = ilObjUser::_search($this);
					break;

				case "grp":
					include_once "./Modules/Group/classes/class.ilObjGroup.php";

					$this->act_type = 'grp';
					$result["grp"] = ilObjGroup::_search($this);
					$result["grp"] = $this->__checkAccess($result["grp"],'grp');
					break;

				case "lm":
					include_once "./Modules/LearningModule/classes/class.ilObjContentObject.php";
					$this->act_type = 'lm';
					$result["lm"][$this->getSearchInByType("lm")] = ilObjContentObject::_search($this,$this->getSearchInByType("lm"));
					$result["lm"][$this->getSearchInByType("lm")]
						= $this->__checkAccess($result["lm"][$this->getSearchInByType("lm")],'lm');
					break;

				case "dbk":
					include_once "./Modules/LearningModule/classes/class.ilObjDlBook.php";
					$this->act_type = 'dbk';
					$result["dbk"][$this->getSearchInByType("dbk")] = ilObjDlBook::_search($this,$this->getSearchInByType("dbk"));
					$result["dbk"][$this->getSearchInByType("dbk")]
						= $this->__checkAccess($result["dbk"][$this->getSearchInByType("dbk")],'dbk');
					break;

				case "role":
					include_once "./classes/class.ilObjRole.php";

					$this->act_type = 'role';
					$result["role"] = ilObjRole::_search($this);

					#$result["role"] = $this->__checkAccess($result["role"],'role');
					break;
			}
		}

		$this->setResult($result);
		$this->__validateResults();

		if ($this->getPerformUpdate())
		{
			$this->__updateDBResult();
		}

		$ilBench->stop("Search", "performSearch");

		return true;
	}

	function getWhereCondition($a_type,$a_fields)
	{
		switch ($a_type)
		{
			case "like":
				$where = $this->__createLikeCondition($a_fields);
				break;

			case "fulltext":
				$where = $this->__createFulltextCondition($a_fields);
				break;
		}

		return $where;
	}

	function getInStatement($a_primary)
	{
		$in = '';

		switch ($this->getSearchType())
		{
			case "new":
				$in .= "";
				break;

			case "result":
#				if(count($this->__getResultIdsByActualType()))
#				{
					$in .= "AND $a_primary IN('".implode("','",$this->__getResultIdsByActualType())."') ";
#				}
				break;

		}

		return $in;
	}

	// PRIVATE METHODS
	function __createLikeCondition($a_fields)
	{
		$where = "WHERE (";
		$concat  = "CONCAT(\" \",";
		$concat .= implode(",\" \",",$a_fields);
		$concat .= ") ";

		$where .= "1 ";

		// AND
		foreach ($this->parsed_str["and"] as $and)
		{
			$where .= "AND ";
			$where .= $concat;
			$where .= "LIKE(\"".$and."\") ";
		}
		
		// AND NOT
		foreach ($this->parsed_str["not"] as $not)
		{
			$where .= "AND ";
			$where .= $concat;
			$where .= "NOT LIKE(\"".$not."\") ";
		}
		// OR
		if (count($this->parsed_str["or"]) and
		   !count($this->parsed_str["and"]) and
		   !count($this->parsed_str["not"]))
		{
			$where .= "AND ( ";

			foreach ($this->parsed_str["all"] as $or)
			{
				$where .= $concat;
				$where .= "LIKE(\"".$or."\") ";
				$where .= "OR ";
			}

			$where .= "0) ";
		}

		$where .= ") ";

		return $where;
	}
	function __createFulltextCondition($a_fields)
	{
		$where = "WHERE (";
		$match = " MATCH(".implode(",",$a_fields).") ";
		
		$where .= "1 ";
		// OR
		if (count($this->parsed_str["or"]))
		{
			$where .= "AND ";
			$where .= $match;
			$where .= " AGAINST('".implode(" ",$this->parsed_str["all"])."') ";
		}
		// AND	
		foreach ($this->parsed_str["and"] as $and)
		{
			$where .= "AND ";
			$where .= $match;
			$where .= "AGAINST('".$and."') ";
		}
		// AND NOT
		/*
		foreach($this->parsed_str["not"] as $and)
		{
			$where .= "AND NOT ";
			$where .= $match;
			$where .= "AGAINST('".$and."') ";
		}
        */
		$where .= ") ";

		return $where;
	}

	function __parseSearchString()
	{
		$tmp_arr = explode(" ",$this->getSearchString());
		$this->parsed_str["and"] = $this->parsed_str["or"] = $this->parsed_str["not"] = array();
		
		foreach ($tmp_arr as $word)
		{
			#$word = trim($word);
			$word = $this->__prepareWord($word);
			if ($word)
			{
				if (substr($word,0,1) == '+')
				{
					$this->parsed_str["all"][] = substr($word,1);
					$this->parsed_str["and"][] = substr($word,1);
					continue;
				}

				if (substr($word,0,1) == '-')
				{
					// better parsed_str["allmost_all"] ;-)
					#$this->parsed_str["all"][] = substr($word,1);
					$this->parsed_str["not"][] = substr($word,1);
					continue;
				}

				if ($this->getCombination() == 'and')
				{
					$this->parsed_str["all"][] = $word;
					$this->parsed_str["and"][] = $word;
					continue;
				}

				if ($this->getCombination() == 'or')
				{
					$this->parsed_str["all"][] = $word;
					$this->parsed_str["or"][] = $word;
					continue;
				}
			}
		}
	}				

	function __validateParsedString(&$message)
	{
		foreach ($this->parsed_str as $type)
		{
			foreach ($type as $word)
			{
				if (strlen($word) < $this->getMinWordLength())
				{
					$to_short = true;
				}
			}
		}

		if ($to_short)
		{
			$message .= ($this->lng->txt('search_to_short').'<br />');
			$message .= ($this->lng->txt('search_minimum_characters').' '.$this->getMinWordLength().'<br />');
						 
			return false;
		}

		return true;
	}

	function __updateDBResult()
	{
		if ($this->getUserId() != 0 and $this->getUserId() != ANONYMOUS_USER_ID)
		{
			$query = "REPLACE INTO usr_search ".
				"VALUES(".$this->ilias->db->quote($this->getUserId()).",'".addslashes(serialize($this->getResults()))."','0')";

			$res = $this->ilias->db->query($query);

			return true;
		}

		return false;
	}
	
	function __readDBResult()
	{
		if ($this->getUserId() != 0 and $this->getUserId() != ANONYMOUS_USER_ID and $this->read_db_result)
		{
			$query = "SELECT search_result FROM usr_search ".
				"WHERE usr_id = ".$this->ilias->db->quote($this->getUserId())." ";

			$res = $this->ilias->db->query($query);

			if ($res->numRows())
			{
				$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
				$this->setResult(unserialize(stripslashes($row->search_result)));
			}
			else
			{
				$this->setResult(array("usr" => array(),
									   "grp" => array(),
									   "lm"  => array(),
									   "dbk" => array()));
			}
		}
		else
		{
			$this->setResult(array("usr" => array(),
								   "grp" => array(),
								   "lm"  => array(),
								   "dbk" => array()));
		}

		$this->__validateResults();
		$this->__updateDBResult();
		return true;
	}

	function __getResultIdsByActualType()
	{
		$results = $this->getResultByType($this->act_type);

		// GET 'content' or 'meta' array
		switch ($this->act_type)
		{

			case "lm":
			case "dbk":
				$results = $results[$this->getSearchInByType($this->act_type)];
				break;
		}

		if(is_array($results))
		{
			foreach ($results as $result)
			{
				$ids[] = $result["id"];
			}
		}
		return $ids ? $ids : array();
	}

	function __checkAccess($a_results,$a_type)
	{
		global $ilAccess;
		
		if (is_array($a_results))
		{
			foreach ($a_results as $result)
			{
				if($ilAccess->checkAccess('read','',$result['id']))
				{
					$checked_result[] = $result;
				}			
			}
		}
		return $checked_result ? $checked_result : array();
	}

	// STATIC
	function _checkParentConditions($a_ref_id)
	{
		include_once './payment/classes/class.ilPaymentObject.php';
		include_once './Modules/Course/classes/class.ilObjCourse.php';

		global $tree,$ilias;

		if(!$tree->isInTree($a_ref_id))
		{
			return false;
		}
		foreach($tree->getPathFull($a_ref_id) as $node_data)
		{
			if(!ilPaymentObject::_hasAccess($node_data['child']))
			{
				return false;
			}
			/*
			if($node_data['type'] == 'crs')
			{
				$tmp_obj =& ilObjectFactory::getInstanceByRefId($node_data['child']);
				$tmp_obj->initCourseMemberObject();

				if(!$tmp_obj->members_obj->hasAccess($ilias->account->getId()))
				{
					return false;
				}
			}
			*/
		}
		return true;
	}

	function __validateResults()
	{
		global $tree;

		$new_result = array();


		// check lm meta

		$this->result['lm']['meta'] = $this->__checkAccess($this->result['lm']['meta'],'lm');
		if(is_array($this->result['lm']['meta']))
		{
			foreach($this->result['lm']['meta'] as $data)
			{
				if($tree->isInTree($data['id']))
				{
					$new_result['lm']['meta'][] = $data;
				}
			}
		}
		$this->result['lm']['content'] = $this->__checkAccess($this->result['lm']['content'],'lm');
		if(is_array($this->result['lm']['content']))
		{
			foreach($this->result['lm']['content'] as $data)
			{
				if($tree->isInTree($data['id']))
				{
					$new_result['lm']['content'][] = $data;
				}
			}
		}
		$this->result['dbk']['meta'] = $this->__checkAccess($this->result['dbk']['meta'],'dbk');
		if(is_array($this->result['dbk']['meta']))
		{
			foreach($this->result['dbk']['meta'] as $data)
			{
				if($tree->isInTree($data['id']))
				{
					$new_result['dbk']['meta'][] = $data;
				}
			}
		}
		$this->result['dbk']['content'] = $this->__checkAccess($this->result['dbk']['content'],'dbk');
		if(is_array($this->result['dbk']['content']))
		{
			foreach($this->result['dbk']['content'] as $data)
			{
				if($tree->isInTree($data['id']))
				{
					$new_result['dbk']['content'][] = $data;
				}
			}
		}
		$this->result['grp'] = $this->__checkAccess($this->result['grp'],'grp');
		if(is_array($this->result['grp']))
		{
			foreach($this->result['grp'] as $data)
			{
				if($tree->isInTree($data['id']))
				{
					$new_result['grp'][] = $data;
				}
			}
		}
		if(is_array($this->result['usr']))
		{
			foreach($this->result['usr'] as $user)
			{
				if($tmp_obj =& ilObjectFactory::getInstanceByObjId($user['id'],false))
				{
					$new_result['usr'][] = $user;
				}
			}
		}
		if(is_array($this->result['role']))
		{
			foreach($this->result['role'] as $user)
			{
				if($tmp_obj =& ilObjectFactory::getInstanceByObjId($user['id'],false))
				{
					$new_result['role'][] = $user;
				}
			}
		}
		$this->setResult($new_result);

		return true;
	}

	function __prepareWord($a_word)
	{
		$word = trim($a_word);
		
		if(!preg_match('/\*/',$word))
		{
			return '%'.$word.'%';
		}
		if(preg_match('/^\*/',$word))
		{
			return str_replace('*','%',$word);
		}
		else
		{
			return '% '.str_replace('*','%',$word);
		}
	}

		
} // END class.ilSearch
?>
