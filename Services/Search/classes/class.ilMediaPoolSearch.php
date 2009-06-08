<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilMediaPoolSearch
*
* Abstract class for test search. Should be inherited by ilFulltextMediaPoolSearch
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* @package ilias-search
*
*/
include_once 'Services/Search/classes/class.ilAbstractSearch.php';

class ilMediaPoolSearch extends ilAbstractSearch
{
	/**
	* Constructor
	* @access public
	*/
	function ilMediaPoolSearch(&$query_parser)
	{
		parent::ilAbstractSearch($query_parser);
	}



	function &performSearch()
	{
		$this->setFields(array('title','description'));

		$and = $this->__createAndCondition();
		$locate = $this->__createLocateString();

		$query = "SELECT DISTINCT(mep_id) as mediapool_id ".
			$locate.
			"FROM object_data,mep_tree ".
			"WHERE obj_id = child ".
			$and." ";

		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->search_result->addEntry($row->mediapool_id,'mep',$this->__prepareFound($row));
		}
		return $this->search_result;
	}
}
?>
