<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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

include_once 'Services/Container/classes/class.ilContainerByTypeContentGUI.php';

/**
* Shows all items grouped by type.
*
* @author Michael Jansen <mjansen@databay.de>
* @version $Id$
*
*/
class ilContainerILincContentGUI extends ilContainerByTypeContentGUI
{
	/**
	* Constructor
	* @access public
	*
	*/
	public function __construct($container_gui_obj)
	{
		parent::__construct($container_gui_obj);
	}
	
	/**
	* Render items list
	*
	* @return	string	html
	* @access	public
	*/
	public function renderItemList()
	{
		global $objDefinition;
		
		$html = '';
	
		$class = $objDefinition->getClassName('icla');
		$location = $objDefinition->getLocation('icla');
		$full_class = 'ilObj'.$class.'ListGUI';
		include_once $location.'/class.'.$full_class.'.php';		
		
		$tpl = $this->newBlockTemplate();
		$first = true;

		$item_html = array();

		if(is_array($this->items['icla']))
		{
			foreach($this->items['icla'] as $key => $item)
			{
				$item_list_gui = new $full_class();
				$item_list_gui->setContainerObject($this);
				if($this->getContainerGUI()->isActiveAdministrationPanel())
				{
					$item_list_gui->enableCheckbox(true);
				}
				
				$html = $item_list_gui->getListItemHTML($this->getContainerObject()->getRefId(),
							$key, $item['name'], $item['description'], $item);						
				if($html != '')
				{					
					$item_html[] = array('html' => $html, 'item_id' => $this->getContainerObject()->getId());
				}
			}

			// output block for resource type
			if(count($item_html) > 0)
			{
				// separator row
				if(!$first)
				{
					$this->addSeparatorRow($tpl);
				}
			
				$first = false;

				// add a header for each resource type
				$this->addHeaderRow($tpl, 'icla');
				$this->resetRowType();
	
				// content row
				foreach($item_html as $item)
				{
					$this->addStandardRow($tpl, $item['html'], $this->getContainerObject()->getId());
				}
			}
		}

		$html = $tpl->get();
		return $html;
	}
}
?>