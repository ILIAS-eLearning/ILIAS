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
* Class ilSearchBaseGUI
*
* Base class for all search gui classes. Offers functionallities like set Locator set Header ...
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* @package ilias-search
*
*/
include_once 'Services/Search/classes/class.ilSearchSettings.php';

class ilSearchBaseGUI
{
	var $settings = null;

	protected $ctrl = null;
	var $ilias = null;
	var $lng = null;
	var $tpl = null;

	/**
	* Constructor
	* @access public
	*/
	function ilSearchBaseGUI()
	{
		global $ilCtrl,$ilias,$lng,$tpl,$ilMainMenu;

		$this->ilias =& $ilias;
		$this->ctrl =& $ilCtrl;
		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->lng->loadLanguageModule('search');

		$ilMainMenu->setActive('search');
		$this->settings =& new ilSearchSettings();
	}

	function prepareOutput()
	{
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.search_base.html",'Services/Search');
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
		ilUtil::infoPanel();

	}
	
	/**
	 * Add Pager
	 *
	 * @access public
	 * @param
	 * 
	 */
	protected function addPager($result,$a_session_key)
	{
	 	global $tpl;
	 	
	 	$_SESSION["$a_session_key"] = max($_SESSION["$a_session_key"],$this->search_cache->getResultPageNumber());
	 	
	 	if($_SESSION["$a_session_key"] == 1 and 
	 		(count($result->getResults()) < $result->getMaxHits()))
	 	{
	 		return true;
	 	}
	 	
		if($this->search_cache->getResultPageNumber() > 1)
		{
			$this->ctrl->setParameter($this,'page_number',$this->search_cache->getResultPageNumber() - 1);
			$this->tpl->setCurrentBlock('prev');
			$this->tpl->setVariable('PREV_LINK',$this->ctrl->getLinkTarget($this,'performSearch'));
			$this->tpl->setVariable('TXT_PREV',$this->lng->txt('search_page_prev'));
			$this->tpl->parseCurrentBlock();
		}
		for($i = 1;$i <= $_SESSION["$a_session_key"];$i++)
		{
			if($i == $this->search_cache->getResultPageNumber())
			{
				$this->tpl->setCurrentBlock('pages_link');
				$this->tpl->setVariable('NUMBER',$i);
				$this->tpl->parseCurrentBlock();
				continue;
			}
			
			$this->ctrl->setParameter($this,'page_number',$i);
			$link = '<a href="'.$this->ctrl->getLinkTarget($this,'performSearch').'" /a>'.$i.'</a> ';
			$this->tpl->setCurrentBlock('pages_link');
			$this->tpl->setVariable('NUMBER',$link);
			$this->tpl->parseCurrentBlock();
		}
		

		if(count($result->getResults()) >= $result->getMaxHits())
		{
			$this->tpl->setCurrentBlock('next');
			$this->ctrl->setParameter($this,'page_number',$this->search_cache->getResultPageNumber() + 1);
			$this->tpl->setVariable('NEXT_LINK',$this->ctrl->getLinkTarget($this,'performSearch'));
		 	$this->tpl->setVariable('TXT_NEXT',$this->lng->txt('search_page_next'));
		 	$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setCurrentBlock('prev_next');
	 	$this->tpl->setVariable('SEARCH_PAGE',$this->lng->txt('search_page'));
	 	$this->tpl->parseCurrentBlock();
	 	
	 	$this->ctrl->clearParameters($this);
	}
	
}
?>
