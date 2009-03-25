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
* Add a search box to main menu
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
*
* @ingroup ServicesSearch
*/
class ilMainMenuSearchGUI
{
	protected $tpl = null;
	protected $lng = null;
	
	private $ref_id = ROOT_FOLDER_ID;
	private $obj_id = 0;
	private $type = '';
	private $isContainer = true;
	
	
	/**
	 * Constructor
	 * @access public
	 */
	public function __construct()
	{
		global $lng,$objDefinition,$tree;
		
		$this->lng = $lng;
		
		if(isset($_GET['ref_id']))
		{
			$this->ref_id = (int )$_GET['ref_id'];
		}
		$this->obj_id = ilObject::_lookupObjId($this->ref_id);
		$this->type = ilObject::_lookupType($this->obj_id);

		if(!$objDefinition->isContainer($this->type))
		{
			$this->isContainer = false;
			$parent_id = $tree->getParentId($this->ref_id);
			$this->obj_id = ilObject::_lookupObjId($parent_id);
			$this->type = ilObject::_lookupType($this->obj_id);
		}
	}
	
	public function getHTML()
	{
		global $ilCtrl;
		
		if(!$this->isContainer)
		{
			return '';
		}
		
		$this->tpl = new ilTemplate('tpl.main_menu_search.html',true,true,'Services/Search');
		$this->tpl->setVariable('FORMACTION','ilias.php?baseClass=ilSearchController&cmd=post'.
			'&rtoken='.$ilCtrl->getRequestToken());
		$this->tpl->setVariable('BTN_SEARCH',$this->lng->txt('search'));
		$this->tpl->setVariable('CONT_REF_ID',$this->ref_id);
		
		return $this->tpl->get();
	} 
}
?>
