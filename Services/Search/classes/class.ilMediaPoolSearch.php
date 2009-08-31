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
		$this->setFields(array('title'));

		$and = $this->__createAndCondition();
		$locate = $this->__createLocateString();

		$query = "SELECT mep_id,obj_id ".
			$locate.
			"FROM mep_tree JOIN mep_item ON child = obj_id ".
			$and;

		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->search_result->addEntry($row->mep_id,'mep',$this->__prepareFound($row),$row->obj_id);
		}
		return $this->search_result;
	}
}
?>
