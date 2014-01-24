<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once './Services/Search/classes/class.ilQueryParser.php';

/**
* search
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version Id$
* 
*/
class ilSearch
{
	protected $qp = null;
	
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
		$this->initQueryParser();
		
		if(!$this->qp->validate())
		{
			$message = $this->qp->getMessage();
			return false;
		}
		return true;
	}

	function performSearch()
	{
		global $objDefinition, $ilBench;

		$ilBench->start("Search", "performSearch");
		
		$this->initQueryParser();

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
					$result['usr'] = $this->performUserSearch();
					break;

				case "grp":
					$result['grp'] = $this->performObjectSearch('grp');
					break;

				case "lm":
					$result['lm'] = $this->performObjectSearch('lm');
					break;

				case "dbk":
					$result['dbk'] = $this->performObjectSearch('dbk');
					break;
					
				case "role":
					$result['role'] = $this->performRoleSearch();
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


	// PRIVATE METHODS

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

	function __updateDBResult()
	{
		global $ilDB;
		
		if ($this->getUserId() != 0 and $this->getUserId() != ANONYMOUS_USER_ID)
		{
			$ilDB->manipulate("DELETE FROM usr_search ".
				"WHERE usr_id = ".$ilDB->quote($this->getUserId() ,'integer')." ".
				"AND search_type = 0 ");

			$ilDB->insert('usr_search',array(
				'usr_id'		=> array('integer',$this->getUserId()),
				'search_result'	=> array('clob',serialize($this->getResults())),
				'checked'		=> array('clob',serialize(array())),
				'failed'		=> array('clob',serialize(array())),
				'page'			=> array('integer',0),
				'search_type'	=> array('integer',0),
				'query'			=> array('text',''),
				'root'			=> array('integer',ROOT_FOLDER_ID)));

			return true;
		}

		return false;
	}
	
	function __readDBResult()
	{
		global $ilDB;
		
		if ($this->getUserId() != 0 and $this->getUserId() != ANONYMOUS_USER_ID and $this->read_db_result)
		{
			$query = "SELECT search_result FROM usr_search ".
				"WHERE usr_id = ".$ilDB->quote($this->getUserId() ,'integer');
			

			$res = $ilDB->query($query);
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
						break;
					}
			}
		}
		return $checked_result ? $checked_result : array();
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
	
	/**
	 * perform a search for users 
	 * @return
	 */
	protected function performUserSearch()
	{
		include_once 'Services/Search/classes/class.ilObjectSearchFactory.php';
		
		$user_search = ilObjectSearchFactory::_getUserSearchInstance($this->qp);
		$res = new ilSearchResult($this->getUserId());
		
		foreach(array("login","firstname","lastname","title",
				"email","institution","street","city","zipcode","country","phone_home","fax") as $field)
		{
			$user_search->setFields(array($field));
			$tmp_res = $user_search->performSearch();
			
			$res->mergeEntries($tmp_res);
		}

		foreach($res->getEntries() as $id => $data)
		{
			$tmp['id'] = $id;
			$users[] = $tmp;
		}
		return $users ? $users : array();
	}
	
	/**
	 * perform object search 
	 * @return
	 */
	protected function performObjectSearch($a_type)
	{
		include_once 'Services/Search/classes/Like/class.ilLikeObjectSearch.php';
		$object_search = new ilLikeObjectSearch($this->qp);
		$object_search->setFilter(array($a_type));
		$res = $object_search->performSearch();
		$res->filter(ROOT_FOLDER_ID,$this->getCombination());
		
		$counter = 0;
		foreach($res->getResultIds() as $id)
		{
			$objs[$counter++]['id'] = $id;				
		}
		return $objs ? $objs : array(); 
	}
	
	/**
	 * 
	 * @param
	 * @return
	 */
	protected function performRoleSearch()
	{
		// Perform like search
		include_once 'Services/Search/classes/Like/class.ilLikeObjectSearch.php';
		$object_search = new ilLikeObjectSearch($this->qp);
		$object_search->setFilter(array('role'));
		
		$res = $object_search->performSearch();
		foreach($res->getEntries() as $id => $data)
		{
			$tmp['id'] = $id;
			$roles[] = $tmp;
		}
		return $roles ? $roles : array();
	}
	
	/**
	 * init query parser 
	 * @return
	 */
	protected function initQueryParser()
	{
		if($this->qp)
		{
			return true;
		}
		
		$this->qp = new ilQueryParser($this->getSearchString());
		$this->qp->setCombination($this->getCombination() == 'and' ? QP_COMBINATION_AND : QP_COMBINATION_OR);
		$this->qp->setMinWordLength($this->getMinWordLength());
		$this->qp->parse();
	}

		
} // END class.ilSearch
?>
