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
* Class ilLMContentSearch
*
* Abstract class for lm content. Should be inherited by ilFulltextLMContentSearch
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* @package ilias-search
*
*/
include_once 'Services/Search/classes/class.ilAbstractSearch.php';

class ilForumSearch extends ilAbstractSearch
{
	/**
	* Constructor
	* @access public
	*/
	function ilForumSearch(&$query_parser)
	{
		parent::ilAbstractSearch($query_parser);
	}

	function &performSearch()
	{
		// Search in topic titles, posting title, posting
		
		// First: search topics:
		$this->setFields(array('thr_subject'));

		$and = $this->__createTopicAndCondition();
		$locate = $this->__createLocateString();

		$query = "SELECT top_frm_fk,thr_pk as frm_id ".
			$locate.
			"FROM  frm_threads,frm_data ".
			"WHERE top_pk = thr_top_fk ".
			$and;

		$res = $this->db->query($query);
		while($row = $res->fetchRow(MDB2_FETCHMODE_OBJECT))
		{
			$thread_post = $row->pos_thr_fk.'_0';
			$this->search_result->addEntry($row->frm_id,'frm',$this->__prepareFound($row),$thread_post);
		}

		// First: search post title, content:
		$this->setFields(array('pos_subject','pos_message'));

		$and = $this->__createPostAndCondition();
		$locate = $this->__createLocateString();

		$query = "SELECT top_frm_fk as frm_id,pos_thr_fk,pos_pk ".
			$locate.
			"FROM  frm_posts,frm_data ".
			"WHERE pos_top_fk = top_pk ".
			$and;

		$res = $this->db->query($query);
		while($row = $res->fetchRow(MDB2_FETCHMODE_OBJECT))
		{
			$thread_post = $row->pos_thr_fk.'_'.$row->pos_pk;
			$this->search_result->addEntry($row->frm_id,'frm',$this->__prepareFound($row),$thread_post);
		}
		return $this->search_result;
	}

	function __createAndCondition()
	{
		echo "Overwrite me!";
	}

}
?>
