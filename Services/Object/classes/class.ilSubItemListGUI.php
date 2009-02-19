<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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
* Base class for all sub item list gui's
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
*
* @ingroup ServicesObject
*/
abstract class ilSubItemListGUI
{
	protected $tpl;
	private $highlighter = null;
	
	private $subitem_ids = array();
	private $item_list_gui;
	private $ref_id;
	private $obj_id;
	private $type;
	
	/**
	 * Constructor 
	 * @param
	 * @return
	 */
	public function __construct()
	{
		 
	}

	/**
	 * set highlighter 
	 * @param
	 * @return
	 */
	public function setHighlighter($a_highlighter)
	{
		$this->highlighter = $a_highlighter;
	}
	
	/**
	 * get highlighter 
	 * @param
	 * @return
	 */
	public function getHighlighter()
	{
		return $this->highlighter;
	}
	
	/**
	 * get ref id 
	 * @return
	 */
	public function getRefId()
	{
		return $this->ref_id;
	}
	
	/**
	 * get obj id 
	 * @return
	 */
	public function getObjId()
	{
		return $this->obj_id;
	}

	/**
	 * get type 
	 * @return
	 */
	public function getType()
	{
		return $this->type;
	}
	
	/**
	 * get sub item ids 
	 * @return
	 */
	public function getSubItemIds()
	{
		return $this->subitem_ids;
	}
	
	/**
	 * get item list gui 
	 * @return
	 */
	public function getItemListGUI()
	{
		return $this->item_list_gui;	 
	}

	/**
	 * init 
	 * @param	
	 * @return
	 */
	public function init($item_list_gui,$a_ref_id,$a_subitem_ids)
	{
		$this->tpl = new ilTemplate('tpl.subitem_list.html',true,true,'Services/Object');
		$this->item_list_gui = $item_list_gui;
		$this->ref_id = $a_ref_id;
		$this->obj_id = ilObject::_lookupObjId($this->getRefId());
		$this->type = ilObject::_lookupType($this->getObjId());
		
		$this->subitem_ids = $a_subitem_ids;
	}
	
	abstract public function getHTML();
	
	
}
?>
