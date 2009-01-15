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

include_once './Services/Search/classes/class.ilAdvancedSearchGUI.php';

/** 
* Meta Data search GUI
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* @ilCtrl_IsCalledBy ilLuceneAdvancedSearchGUI: ilSearchController
*
* @ingroup ServicesSearch
*/
class ilLuceneAdvancedSearchGUI extends ilSearchBaseGUI
{
	protected $ilTabs;
	
	/**
	 * Constructor 
	 */
	public function __construct()
	{
		global $ilTabs;
		
		$this->tabs_gui = $ilTabs;
		parent::__construct();
	}
	
	/**
	 * Execute Command 
	 */
	public function executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		$this->prepareOutput();
		switch($next_class)
		{
			default:
				if(!$cmd)
				{
					$cmd = "showSavedResults";
				}
				$this->$cmd();
				break;
		}
		return true;
	}

	/**
	 * Show saved results 
	 * @return
	 */
	public function showSavedResults()
	{
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.lucene_adv_search.html','Services/Search');
	}
	
	/**
	 * Prepare output 
	 */
	public function prepareOutput()
	{
		parent::prepareOutput();
		$this->getTabs();
	}
	
	/**
	 * get tabs 
	 */
	protected function getTabs()
	{
		$this->tabs_gui->addTarget('search',$this->ctrl->getLinkTargetByClass('illucenesearchgui'));
		$this->tabs_gui->addTarget('search_advanced',$this->ctrl->getLinkTarget($this));
		
		$this->tabs_gui->setTabActive('search_advanced');
	}
}
?>
